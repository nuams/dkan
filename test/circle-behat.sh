for var in "$@"
do
    echo "$var"
    bin/behat --format=pretty --out=std --format=junit --out=$CIRCLE_ARTIFACTS/junit --dry-run $var
done
