#! /bin/bash -e

if [[ $1 == "-h" || $1 == "--help" ]] ; then
	echo "Usage:  ./updatepos.sh"
	echo ""
	echo "Updates each .po file in the directory with the latest English"
	echo "strings in the .pot file."
	echo ""
	echo "To add a translation, do the following before calling updatepos.sh:"
	echo "    touch <plugin-id>-<lc>_<CC>.mo"
	echo ""
	echo "THIS IS STEP 2:  This is the second step in translating a plugin."
	echo "The prior step is './makepot.sh.'"
	echo "The next step is './makemos.sh.'"
	echo ""
	echo "For more information on translating your plugin, see"
	echo "http://codex.wordpress.org/I18n_for_WordPress_Developers"
	echo ""
	echo "Author: Daniel Convissor <danielc@analysisandsolutions.com>"
	echo "License: http://www.gnu.org/licenses/gpl-2.0.html"
	echo "http://wordpress.org/extend/plugins/oop-plugin-template-solution/"
	exit 1
fi

cd "`dirname "$0"`"

while read file ; do
	echo "Merging $file..."
	msgmerge -vUN --backup=off $file oop-plugin-template-solution.pot
done < <(ls *po)
