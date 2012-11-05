#! /bin/bash -e

if [[ $1 == "-h" || $1 == "--help" ]] ; then
	echo "Usage:  ./makemos.sh"
	echo ""
	echo "Creates or updates a binary .mo file for each .po file."
	echo ""
	echo "THIS IS THE LAST STEP:  This compltetes the plugin translation."
	echo "The prior step is './updatepos.sh.'"
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
	lang=${file%*.po}
	echo "Building $lang..."
	msgfmt -o $lang.mo $lang.po
done < <(ls *po)
