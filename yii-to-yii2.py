#!/usr/bin/env python3
import argparse, os, re, glob, fnmatch, sys, subprocess, shutil

## Settings

default_code_paths = ["controllers/", "commands/", "views/", "models/", "components/", "RISParser/", "tests/acceptance/"]

## Constants (alter if necessary)

fnmatch_pattern = '*.php'
php_class = "[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*"

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
        "yii\\helpers\\": ["Html", "Url"]
    }

    for i, k in namespaces.items():
        sources[k] = ["".join(i.split(".")[:-1]) for i in os.listdir(i)]

    return sources

exclude = ['parent', 'self', "static"]

## The actual code

def open_wrapper(filepath, flags):
    if sys.version_info.major > 2:
        return open(filepath, flags, encoding="utf8")
    else:
        return open(filepath, flags)

def convert_folderstructure():
    if not os.path.isdir("protected/") or not os.path.isdir("web/"):
        raise Exception("The directories protected/ and web/ have to exist for the conversion to work.")
    
    print("running composer commands ...\n")
    commands = [
        "composer global require 'fxp/composer-asset-plugin:~1.1.1'",
        "composer create-project --prefer-dist yiisoft/yii2-app-basic yii2-template",
        "composer require yiisoft/yii2",
        "composer require --dev yiisoft/yii2-debug"
        "composer require --dev yiisoft/yii2-gii"
    ]
    
    for command in commands:
        print(subprocess.Popen(command, shell=True, stdout=subprocess.PIPE).stdout.read())
    
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
    created_assets_dir = False
    if not os.path.exists("web/assets"):
        os.mkdir("web/assets")
        created_assets_dir = True

    # make git recognise the new folder struture
    with open(".gitignore", 'r') as gitignore:
        ignores = gitignore.readlines()

    for i, elem in enumerate(ignores):
        ignores[i] = re.sub("^html/",      "web/", elem)
        ignores[i] = re.sub("^protected/", "",     elem)

    if not "config/web.php\n" in ignores and not "config/web.php/\n" in ignores:
        ignores.append("config/web.php\n")

    if not "web/assets\n" in ignores and not "web/assets/\n" in ignores:
        ignores.append("web/assets/\n")

    with open(".gitignore", 'w') as gitignore:
        gitignore.writelines(ignores)

def do_replace(filepath, yii1_classes, replacements):
    """
    Applies the replacements named in `replacements` in the the file `filepath`
    """
    print("Processing " + filepath)
    with open_wrapper(filepath, 'r') as file:
        contents = file.read()
    
    if "yii-app" in replacements:
        contents = contents.replace("Yii::app()", "Yii::$app")
    
    if "html-web" in replacements:
        contents = contents.replace("html/", "web/")
    
    if "yii1-classes" in replacements:
        for i in yii1_classes:
            contents = re.sub("([^\\w\\d])" + i, "\\1" + i[1:], contents)
    
    if "create-url" in replacements:
        contents = contents.replace("Yii::\$app\->createUrl\(", "Url::to(")
        contents = contents.replace("$this->createUrl(",        "Url::to(")
    
    if "static-table-name" in replacements:
        contents = contents.replace("public function tableName()", "public static function tableName()")
    
    if "active-query" in replacements:
        contents = contents.replace("::model()", "::find()")
        # Replace functions of CAtiveRecord
        # This does not replace expressions with nested parentheses
        contents = contents.replace("::find()->findAll()", "::findAll()")
        
        contents = re.sub(r"::find\(\)->findByPk\(([^\(\)]+)\)", r"::findOne(\1)", contents)
        if "::find()->findByPk(" in contents:
            print("warn: occurences of findByPk() were not replace, probably because of nested parentheses.")
    
        contents = re.sub(r"::find\(\)->findByAttributes\(\[([^\(\)\[\]]+)\]\)", r"::findOne([\1])", contents)
        if "::find()->findByAttributes(" in contents:
            print("warn: occurences of findByPk() were not replace, probably because of nested parentheses.")
        
        contents = re.sub(r"::find\(\)->findAllByAttributes\(\[([^\(\)\[\]]+)\]\)", r"::findAll([\1])", contents)
        if "::find()->findByAttributes(" in contents:
            print("warn: occurences of findByPk() were not replace, probably because of nested parentheses.")
    
    if "this-context" in replacements:
        contents = re.sub(r"\$this->(context->)*", "$this->context->", contents)
        contents = contents.replace("$this->context->pageTitle", "$this->title")
        contents = re.sub(r"\$this->context->(title|render)", r"$this->\1", contents)
    
    with open_wrapper(filepath, 'w') as file:
        file.write(contents)

def find_yii1_classes():
    """
    Finds the names of the classes that are specific to yii1 and stores them in
    a file called "yii1-classes". You shouldn't need to run that script.
    
    Requirement: Yii1 can be found in ../yii1/
    """
    with open_wrapper("yii1-classes.txt", 'w') as file:
        for root, dirnames, filenames in os.walk("../yii1/framework/"):
            for filename in filenames:
                if re.match("C[A-Z]\w\w.*\\.php", filename):
                    file.write(filename[:-4] + "\n")

def get_all_files(paths):
    """
    Yields all files in `paths` that match `fnmatch_pattern`.
    
    `paths` might contain anarbitrary count of files and folders.
    """
    # Pretty python >= 3.5 solution:
    #for filename in glob.iglob(path + "**/*.php", recursive=True):
    #    yield filename
    
    for path in paths:
        if os.path.isfile(path):
            yield path
        else:
            for root, dirnames, filenames in os.walk(path):
                for filename in fnmatch.filter(filenames, fnmatch_pattern):
                    yield os.path.join(root, filename)

def find_usages_for_import(filepath, sources):
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

def insert_imports(filepath, imports):
    """
    Inserts the lines given in `imports` at the top of the file given in
    `filepath` while attempting to create some nice formatting
    """
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
    parser.add_argument('--replace', nargs='+')
    parser.add_argument('--import-usages', action='store_true')
    parser.add_argument('--path', default=None)
    args = parser.parse_args()
    paths = [args.path] if args.path else default_code_paths
    
    if not args.yii2_template and not args.import_usages and not args.replace:
        parser.print_help()
    
    print(sys.version_info)
    
    if args.yii2_template:
        convert_folderstructure()
    if args.replace:
        with open("yii1-classes.txt", 'r') as file:
            yii1_classes = [i.strip() for i in file.readlines()]
        for filepath in get_all_files(paths):
            do_replace(filepath, yii1_classes, args.replace)
    if args.import_usages:
        sources = generate_namespace_mapping()
        for filepath in get_all_files(paths):
            imports = find_usages_for_import(filepath, sources)
            insert_imports(filepath, imports)
    
if __name__ == "__main__":
    main()
