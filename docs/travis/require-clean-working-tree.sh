# http://stackoverflow.com/a/5148851
if [[ `git status --porcelain` ]]; then
  git status # debug information
  exit 1
fi
