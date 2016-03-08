#!/usr/bin/env python3
"""
This is a script to help converting a yii1 project into a yii2 project
"""

import argparse, os, re, glob, fnmatch, sys, subprocess, shutil

## Settings

default_code_paths = ["controllers/", "commands/", "views/", "models/", "components/", "RISParser/", "tests/acceptance/"]

def generate_namespace_mapping():
    """
    Defines the locations where classes should be imported from. Returns a map
    in the form {"namespace": ["class1", "class2", ...], ...}
    """
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
    if not os.path.isdir("protected/") or not os.path.isdir("html/"):
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

def find_usages_for_import(contents, sources):
    """
    Searches for all classes to be imported using the searcher and finds the
    matching namespace. It returns a list of lines that should be inserted at
    the top of the PHP file to declare the right namespace and the required
    import those classes.
    """
    php_class = "[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*"

    searcher = [
        # finds all classnames without namespace followed by ::
        # [^\$\w] catches local variables with static bindings
        # \\\\ matches a single backslash to exclude classes already using a namespace
        re.compile("[^\$\w\\\\](" + php_class + ")::"),
        # finds the names of the baseclasses for files defining inherited classes
        re.compile("class " + php_class + " extends (" + php_class + ")"),
    ]
    
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
            print("Failed to find namespace in " + filepath + ": " + classname)

    imports[2:] = sorted(imports[2:])
    return imports

def insert_imports(contents, imports, filepath = ""):
    """
    Inserts the lines given in `imports` at the top of the file given in
    `filepath` while attempting to create some nice formatting and discarding
    old namespaces and imports
    """
    if len(imports) == 0:
        return 
    
    if filepath:
        print("Inserting imports into " + filepath)

    contents = [i + "\n" for i in contents.split("\n")]
    new_contents = ""

    # ensure the first line starts with "<?php"
    if contents[0].startswith("<?php"):
        new_contents += contents.pop(0)
        new_contents += "\n"
        new_contents += "".join(imports)
        new_contents += "\n"
    else:
        new_contents += "<?php\n\n"
        new_contents += "".join(imports)
        new_contents += "\n?>\n\n"

    # Strip old namespaces, imports and some newlines at the top
    contents = list(filter(lambda line: not line.startswith("use ") and not line.startswith("namespace "),  contents))

    while len(contents) > 0 and contents[0].strip() == "":
        contents.pop(0)

    if len(contents) == 0:
        print("Warn: File '" + filepath + "' is empty")

    new_contents += contents
    
    return new_contents

def relations(contents, filepath = ""):
    """
    Find the old relation function, removes it including an optional preceeding
    comment and insert yii2-relation functions instead. Note that all strings
    inside the relation function that match the pattern for a relation are taken
    for actual relations and are therefore represented in the new relations
    function. This might e.g. reactivate a commented out relation.
    """
    # matches the a function with a preceeding well-formatted comment
    function_regex = r"\n(?: */\*(?:[^\*]|\*[^/])*\*/\n)?([\ \n]*public *function *{}\(\)[\ \n]*{{[^}}]*}})\ *\n"

    # matches the relations function
    relation_regex = function_regex.format("relations")

    relation_head = """
    /**
     * @return \yii\db\relation
     */
    public function get{}()
    {{
        return """

    relation_tail = """
    }}
    """

    # old relations
    word = r"\s*['\"](\w+)['\"]\s*"
    relation_belongs_to = word + r"=>\s*(?:array\(|\[)self::BELONGS_TO," + word + r"," + word + r"\s*(?:\)|\])"
    relation_has_many   = word + r"=>\s*(?:array\(|\[)self::HAS_MANY,"   + word + r"," + word + r"\s*(?:\)|\])"
    relation_many_many  = word + r"=>\s*(?:array\(|\[)self::MANY_MANY,"  + word + r"," + r"\s*['\"](\w+)\((\w+),\s*(\w+)\)['\"]\s*" + r"\s*(?:\)|\])"

    # new relations
    relation_one       = relation_head + "$this->hasOne({}::className(), ['id' => '{}']);" + relation_tail
    relation_many      = relation_head + "$this->hasMany({}::className(), ['{}' => 'id']);" + relation_tail
    relation_many_many = relation_head + "$this->hasMany({}::className(), ['id' => '{}'])->viaTable('{}', ['{}' => 'id']);" + relation_tail

    if not re.search(relation_regex, contents):
        return

    if filepath:
        print("Found relations in " + filepath)

    # Remove old getters that would conflict with the new ones
    for relation in [relation_belongs_to, relation_has_many, relation_many_many]:
        for i in re.findall(relation, contents):
            contents = re.sub(function_regex.format("get" + i[0][0].upper() + i[0][1:]), "", contents)

    # Remove the old relations method by splitting
    splitted = re.split(relation_regex, contents)
    assert len(splitted) == 3
    contents = splitted[0]

    relation_function = splitted[1]

    for relation_old, relation_new in [(relation_belongs_to, relation_one), (relation_has_many, relation_many), (relation_many_many, relation_many_many)]:
        for i in re.findall(relation_old, relation_function):
            i = list(i)
            i[0] = i[0][0].upper() + i[0][1:] # capitalize first letter
            contents += "".join(relation_new.format(*i))

    contents += splitted[2]

    return contents

def activequery(contents, filepath = ""):
    """
    This replaces the most important functions of CActiveRecord with the
    corresponding ActiveRecord functions. It tries to find matching brackets,
    so e.g. having "'('" as a (nested) argument inside the find*() call will
    likely make the replace fail.
    
    The replaced functions currently are:
     * findAll()
     * findByPk()
     * findByAttributes()
     * findAllByAttributes()
    """
    #if contents.find("::model()") == -1:
    #    return contents

    if filepath:
        print("Processing ActiveQueries in " + filepath)
    
    contents_copy = contents
    
    def find_matching(contents, begin, open, close):
        brackets = 1
        pos = begin
        while brackets > 0:
            if contents[pos] == open:
                brackets += 1
            elif contents[pos] == close:
                brackets -= 1
            pos += 1
            if pos == len(contents):
                print("Error: Couldn't match parentheses. Using workaround.")
                return -1
        return pos

    activequeries = [("::find()->findByPk(",            "::findOne("),
                     ("::find()->findByAttributes(",    "::findOne("),
                     ("::find()->findAllByAttributes(", "::findAll(")]
    
    contents = contents.replace("::model()", "::find()")

    contents = contents.replace("::find()->findAll()", "::find()->all()")
    
    for (expression, replacement_expression) in activequeries:
        begin = contents.find(expression)
        while begin != -1:
            end = find_matching(contents, begin + len(expression), '(', ')')
            if end == -1: # Error in find_matching -> try simple replace
                old = contents[begin:begin+len(expression)]
                new = replacement_expression
            else:
                old = contents[begin:end]
                new = replacement_expression + contents[begin + len(expression):end]
            
            print("Replaced " + old + " with " + new)
            contents = contents.replace(old, new)

            begin = contents.find(expression)
    
    return contents_copy

def do_replace(contents, yii1_classes, replacements, filepath):
    """
    Applies the replacements named in `replacements` in the the file `filepath`
    """
    if filepath:
        print("Processing " + filepath)

    if "yii-app" in replacements:
        contents = contents.replace("Yii::app()", "Yii::$app")

    if "html-web" in replacements:
        contents = contents.replace("html/", "web/")

    if "yii1-classes" in replacements:
        for i in yii1_classes:
            contents = re.sub("([^\\w\\d])" + i, "\\1" + i[1:], contents)

    if "create-url" in replacements:
        contents = contents.replace("Yii::$app->createUrl(", "Url::to(")
        contents = contents.replace("$this->createUrl(",     "Url::to(")
        contents = contents.replace("Html::link(",           "Html::a(")

    if "static-table-name" in replacements:
        contents = contents.replace("public function tableName()", "public static function tableName()")

    if "this-context" in replacements:
        contents = re.sub(r"\$this->(context->)*", "$this->context->", contents)
        contents = contents.replace("$this->context->pageTitle", "$this->title")
        contents = re.sub(r"\$this->context->(title|render|beginContent|endContent)", r"$this->\1", contents)

    if "render" in replacements:
        contents = contents.replace("$this->render(", "return $this->render(")
        contents = contents.replace("$this->renderPartial(", "echo $this->render(")
        # Catch replacing correct render() call
        contents = contents.replace("echo return $this->render(", "echo $this->render(")
        contents = contents.replace("return return $this->render(", "return $this->render(")

    if "layout" in replacements:
        contents = re.sub(r"public *\$layout *= *(['\"])//layouts/", r"public $layout = \1@app/views/layouts/", contents)
        contents = re.sub(r"\$this->context->layout *= *(['\"])//layouts/", r"$this->context->layout = \1@app/views/layouts/", contents)
        contents = re.sub(r"\$this->(begin|end)Content\((['\"])//layouts/", r"$this->\1Content(\2//layouts/", contents)

    return contents

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
    Yields all files in `paths` that match "*.php"

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
                for filename in fnmatch.filter(filenames, "*.php"):
                    yield os.path.join(root, filename)

def main():
    parser = argparse.ArgumentParser(description='Yii1 to Yii2 conversion helper. This tool offer the most changes required to update as commands.')
    parser.add_argument('--yii2-template', action='store_true', help='Initial-step. Changes the folderstrucuture to the yii2 basic template and also updates .gitignore so you commit the changes')
    parser.add_argument('--import-usages', action='store_true', help='Adds the namespace and most required `use` directives at the beginning of all php files')
    parser.add_argument('--relations', action='store_true', help='Replaces the old relations() method with new yii2 style relations.')
    parser.add_argument('--activequery', action='store_true', help='TODO')
    parser.add_argument('--replace', nargs='+', help='Apply named replaces FIXME: list of the available replaces')
    parser.add_argument('--path', default=None, help='A directory or a file to which actions will be applied. Doesn\'t apply on --yii2-template')
    args = parser.parse_args()

    paths = [args.path] if args.path else default_code_paths

    if not args.yii2_template and not args.relations and not args.activequery and not args.replace and not args.import_usages:
        parser.print_help()

    if args.yii2_template:
        convert_folderstructure()

    if args.relations:
        for filepath in get_all_files(paths):
            with open_wrapper(filepath, 'r') as file:
                contents = file.read()
            contents = relations(contents, filepath)
            with open_wrapper(filepath, 'w') as file:
                file.write(contents)

    if args.activequery:
        for filepath in get_all_files(paths):
            with open_wrapper(filepath, 'r') as file:
                contents = file.read()
            contents = activequery(contents, filepath)
            with open_wrapper(filepath, 'w') as file:
                file.write(contents)

    if args.replace:
        with open("yii1-classes.txt", 'r') as file:
            yii1_classes = [i.strip() for i in file.readlines()]
        for filepath in get_all_files(paths):
            with open_wrapper(filepath, 'r') as file:
                contents = file.read()
            contents = do_replace(contents, yii1_classes, args.replace, filepath)
            with open_wrapper(filepath, 'w') as file:
                file.write(contents)
            

    if args.import_usages:
        sources = generate_namespace_mapping()
        for filepath in get_all_files(paths):
            with open_wrapper(filepath, 'r') as file:
                contents = file.read()
            imports = find_usages_for_import(contents, sources)
            contents = insert_imports(contents, imports, filepath)
            with open_wrapper(filepath, 'w') as file:
                file.write(contents)

if __name__ == "__main__":
    main()
