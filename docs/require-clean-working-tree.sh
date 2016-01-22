# http://stackoverflow.com/a/5148851
if [[ `git status --porcelain` ]]; then
  exit 1
fi
