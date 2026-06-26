bugs: issue list --label=bug
igrep: '!gh issue list --label="$1" | grep "$2"'
features: |-
    issue list
    --label=enhancement