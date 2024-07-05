#!/bin/bash

projectPath="$(dirname "${0}")/../.." \
&& cd "${projectPath}" \
&& projectPath="$(pwd)" \
|| exit 1

[ -z "${ANTLR_BIN}" ] && ANTLR_BIN=antlr4

versionString="$(${ANTLR_BIN} 2>&1 \
	| grep 'ANTLR Parser Generator  Version ' \
	| sed -E 's,.*Version ([1-9][0-9]*\.[0-9][0-9]*\.[0-9][0-9]*).*,\1,g' \
)"
major="$(cut -d'.' -f 1 <<< "${versionString}")"
minor="$(cut -d'.' -f 2 <<< "${versionString}")"
patch="$(cut -d'.' -f 3 <<< "${versionString}")"

baseNamespace="NoreSources\\Data\\Parser\\ANTLR\\"
baseTarget="${projectPath}/src/Parser/ANTLR"
version="v${major}$(printf '%02d' ${minor})00"
if [ ! -z "${1}" ]
then
	version="${1}"
fi

baseNamespace="${baseNamespace}${version}\\"
baseTarget="${baseTarget}/${version}"

while read f
do
	d="$(basename "$(dirname "${f}")")"
	namespace="${baseNamespace}${d}"
	target="${baseTarget}/${d}"
	mkdir -p "${target}"
	${ANTLR_BIN} -Dlanguage=PHP \
		-package "${namespace}" \
		-o "${target}" \
		"${f}"
		
done << EOF
$(find "${projectPath}/resources/antlr" -type f -name '*.g4')
EOF
  
