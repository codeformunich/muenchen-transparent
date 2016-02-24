#/usr/bin/env python3.5
import argparse, os, re, glob, fnmatch, sys, subprocess, shutil

## Settings

print(sys.version_info)

default_code_paths = ["controllers/", "views/", "models/", "components/", "RISParser/", "tests/acceptance/"]

## Constants (alter if necessary)

fnmatch_pattern = '*.php'
php_class = "[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*"

global_replaces = {
    "Yii::app\(\)": "Yii::$app",
    "html/": "web/"
}

searcher = [
    # finds all classnames without namespace followed by ::
    # [^\$\w] catches local variables with static bindings
    # \\\\ matches a single backslash to exclude classes already using a namespace
    re.compile("[^\$\w\\\\](" + php_class + ")::"),
    # finds the names of the baseclasses for files defining inherited classes
    re.compile("class " + php_class + " extends (" + php_class + ")"),
]

def generate_namespace_mapping():
    namespaces = {
        "controllers": "app\\controllers\\",
        "models": "app\\models\\",
        "components": "app\\components\\",
        "RISParser": "app\\risparser\\",
    }

    sources = {
        "": ["Solarium"],
        "": ["Yii"],
        "yii\\web\\": ["Controller"],
        "yii\\db\\": ["ActiveRecord"],
        "yii\\helpers\\": ["Html"]
    }

    for i, k in namespaces.items():
        sources[k] = ["".join(i.split(".")[:-1]) for i in os.listdir(i)]

    return sources

exclude = ['parent', 'self', "static"]

## The actual code

def convert_folderstructure():
    if not os.path.isdir("protected/") or not os.path.isdir("html/"):
        raise Exception("The directories protected/ and html/ have to exist for the conversion to work.")
    
    print("running composer commands ...\n")
    commands = [
        "composer global require 'fxp/composer-asset-plugin:~1.1.1'",
        "composer create-project --prefer-dist yiisoft/yii2-app-basic yii2-template",
        "composer require yiisoft/yii2",
        "composer require --dev yiisoft/yii2-debug"
    ]
    
    for command in commands:
        break
        print subprocess.Popen(command, shell=True, stdout=subprocess.PIPE).stdout.read()
    
    print("finished running composer commands.\n")
    
    # get all the interesting files form the yii2 basic app template
    shutil.move("html/", "web/")
    shutil.move("yii2-template/web/index.php", "web/index.php")
    shutil.move("yii2-template/web/index-test.php", "web/index-test.php")
    shutil.move("yii2-template/yii", "yii")
    shutil.move("yii2-template/yii.bat", "yii.bat")
    shutil.move("yii2-template/config/", "config-new/")
    shutil.move("yii2-template/runtime/", "runtime/")
    shutil.rmtree("yii2-template")

    # get the code out of protected/ into the root dir
    for i in os.listdir("protected/"):
        path = os.path.join("protected/", i)
        if os.path.isdir(path):
            shutil.move(path, i)
    
    shutil.rmtree("protected/")

    # make git recognise the new folder struture
    with open(".gitignore", 'r') as gitignore:
        contents = gitignore.readlines()

    gitignore = open(".gitignore", 'w')
    for i in contents:
        i = re.sub("^html/", "web/", i)
        i = re.sub("^protected/", "", i)
        gitignore.write(i)
    gitignore.close()

def do_replace(filepath, yii1_classes):
    print("Processing " + filepath)
    with open_wrapper(filepath, 'r') as file:
        contents = file.read()
    
    for search, replace in global_replaces.items():
        new_contents = re.sub(search, replace, contents)
        if new_contents != contents:
            print("Replaced by " + search)
            contents = new_contents
    
    for i in yii1_classes:
        new_contents = contents.replace(" " + i, " " + i[1:])
        if new_contents != contents:
            print("Replaced class " + i)
            contents = new_contents
    
    with open_wrapper(filepath, 'w') as file:
        file.write(contents)

def find_yii1_classes():
    with open_wrapper("yii1-classes.txt", 'w') as file:
        for root, dirnames, filenames in os.walk("../yii1/framework/"):
            for filename in filenames:
                if re.match("C[A-Z].*\\.php", filename):
                    file.write(filename[:-4] + "\n")

def get_all_files(paths):
    """
    Yields all files in `paths` that match `fnmatch_pattern`.
    
    `paths` might contain anarbitrary count of files and folders.
    """
    # Pretty python >= 3.5 solution
    #for filename in glob.iglob(path + "**/*.php", recursive=True):
    #    yield filename
    
    for path in code_paths:
        if os.path.isfile(path):
            yield path
        else:
            for root, dirnames, filenames in os.walk(path):
                for filename in fnmatch.filter(filenames, fnmatch_pattern):
                    yield os.path.join(root, filename)

def find_usages_for_import(filepath):
    """
    Searches for all classes to be imported using the `searcher` and finds the
    matching namespace. It returns a list of lines that should be inserted at
    the top of the PHP file to declare the right namespace and the required 
    import those classes.
    """
    with open_wrapper(filepath, 'r') as f:
        contents = f.read()
    
    results = []
    for regex in searcher:
        results += regex.findall(contents)
    
    imports = []
    
    # If required, declare a namespace
    defined_class = os.path.splitext(os.path.basename(filepath))[0]
    for namespace, classes in sources.items():
        if defined_class in classes:
            if namespace[-1] == "\\":
                namespace = namespace[:-1]
            imports.append("namespace " + namespace + ";\n")
            imports.append("\n"); # formatting
            break

    for classname in results:
        if classname in exclude or classname == defined_class:
            continue

        found = False;
        for namespace, classes in sources.items():
            if classname in classes:
                text = "use " + namespace + classname + ";\n"
                if not text in imports:
                    imports.append(text)
                found = True;
                break

        if not found:
            print("failed to find namespace in " + filepath + ": " + classname)
    
    imports[2:] = sorted(imports[2:])
    return imports

def insert_imports_and_namespace(filepath, imports, sources):
    with open_wrapper(filepath, 'r') as file:
        contents = file.readlines()
    
    file = open_wrapper(filepath, 'w')
    
    # ensure the first line starts with "<?php"
    if contents[0].startswith("<?php"):
        file.write(contents.pop(0))
        file.write("\n")
        file.writelines(imports)
        file.write("\n")
    else:
        file.write("<?php\n\n")
        file.writelines(imports)
        file.write("\n?>\n\n")
    
    # write back the old file contents minus imports and some newlines at the top
    contents = list(filter(lambda line: not line.startswith("use ") and not line.startswith("namespace "),  contents))
    
    while len(contents) > 0 and contents[0].strip() == "":
        contents.pop(0)
    
    if len(contents) == 0:
        print("Warn: File '" + filepath + "' is empty")
        return
    
    file.writelines(contents)
    
    file.close()

def main():
    parser = argparse.ArgumentParser(description='Semi-automatically converts a yii1 to a yii2 project')
    parser.add_argument('--yii2-template', action='store_true', help='Initial-step. Changes the folderstrucuture to the yii2 basic template and also updates .gitignore so you commit the changes')
    parser.add_argument('--replace',       action='store_true')
    parser.add_argument('--import-usages', action='store_true')
    parser.add_argument('--path', default=None)
    args = parser.parse_args()
    print(args)
    paths = [args.path] if args.path else default_code_paths
    
    if args.yii2_template:
        convert_folderstructure()
    if args.replace:
        with open("yii1-classes.txt", 'r') as file:
            yii1_classes = [i.strip() for i in file.readlines()]
        for filepath in get_all_files(files):
            do_replace(filepath, yii1_classes)
    if args.import_usages:
        sources = generate_namespace_mapping()
        for filepath in get_all_files(files):
            imports = find_usages_for_import()
            insert_imports_and_namespace(filepath, imports, sources)
            
if __name__ == "__main__":
    main()
