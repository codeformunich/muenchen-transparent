#/usr/bin/env python3.5
import os, re, glob, fnmatch, sys, subprocess

## Settings

print(sys.version_info)

code_paths = ["controllers/", "views/", "models/", "components/", "RISParser/", "tests/acceptance/"]

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

sources = generate_namespace_mapping()
exclude = ['parent', 'self', "static"]

with open("yii1-classes.txt", 'r') as file:
    yii1_classes = [i.strip() for i in file.readlines()]

## The actual code

def install_yii2():
    commands = [
        "composer global require 'fxp/composer-asset-plugin:~1.1.1'",
        "composer create-project --prefer-dist yiisoft/yii2-app-basic yii2-template",
        "composer require yiisoft/yii2",
        "composer require --dev yiisoft/yii2-debug"
    ]
    
    for command in commands:
        print subprocess.Popen(command, stdout=PIPE).stdout.read()
    
    shutil.move("yii2-template/config/", "config/")
    shutil.mkdir("runtime/")
    shutil.move("html/", "web/")
    shutil.move("yii2-template/web/index.html", "web/index.html")
    shutil.move("yii2-template/web/index_test.html", "web/index_test.html")
    shutil.move("yii2-template/yii", "yii")
    shutil.move("yii2-template/yii.bat", "yii.bat")
    shutil.rmtree("yii2-template")

def open_wrapper(filepath, flags):
    if sys.version_info.major < 3:
        return open(filepath, flags) # TODO Test this version on windows
    else:
        return open(filepath, flags, encoding="utf8")

def do_replace(filepath):
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

def get_all_code_files():
    """
    Yields all files that match `fnmatch_pattern` in `code_paths` and its subdirectories
    """
    if sys.version_info >= (3, 5):
        for path in code_paths:
            for filename in glob.iglob(path + "**/*.php", recursive=True):
                yield filename
    else:
        print("Using the code for python < 3.5")
        for path in code_paths:
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

def insert_imports_and_namespace(filepath, imports):
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

def replace_all():
    for filepath in get_all_code_files():
        do_replace(filepath)

def import_all():
    for filepath in get_all_code_files():
        imports = find_usages_for_import(filepath)
        insert_imports_and_namespace(filepath, imports)

if __name__ == "__main__":
    replace_all()
    import_all()
