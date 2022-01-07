#!/usr/bin/env bash

set -euxo pipefail

# https://stackoverflow.com/a/246128/1585343
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

RELEASES_FILE="$(mktemp)"
INVALID_BUFFER="$(mktemp)"

rm_tmp_file() {
  rm ${RELEASES_FILE}
  rm ${INVALID_BUFFER}
}

trap "rm_tmp_file" ERR

cat "${DIR}/repo-pass-1.html" \
| pup 'a[href$=".zip"] attr{href}' \
| sort \
| uniq \
> ${RELEASES_FILE}

cat ${RELEASES_FILE} \
| jq --raw-input --slurp 'split("\n") | map(select(. != ""))' \
> ${DIR}/wordpress-download-urls.json

# valid stuff
cat ${RELEASES_FILE} \
| grep -v 'wordpress-mu' \
| grep -v "\-IIS.zip" \
| grep -v "wordpress-[0123]" \
| jq --raw-input --slurp 'split("\n") | map(select(. != ""))' \
> ${DIR}/wordpress-valid-download-urls.json

# invalid stuff
cat ${RELEASES_FILE} \
| grep 'wordpress-mu' \
>> ${INVALID_BUFFER}

cat ${RELEASES_FILE} \
| grep '\-IIS.zip' \
>> ${INVALID_BUFFER}

cat ${DIR}/wordpress-weird-urls.txt >> ${INVALID_BUFFER}

cat ${INVALID_BUFFER} \
| jq --raw-input --slurp 'split("\n") | map(select(. != ""))' \
> ${DIR}/wordpress-invalid-download-urls.json

rm_tmp_file
