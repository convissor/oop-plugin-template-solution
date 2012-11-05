#! /bin/bash -e

if [[ $1 == "-h" || $1 == "--help" ]] ; then
	echo "Usage:  ./makepot.sh"
	echo ""
	echo "Scans your plugin for strings that need translating and puts"
	echo "those strings in the .pot file.  Creates the .pot file if doesn't"
	echo "exist; updates the .pot file if it does."
	echo ""
	echo "THIS IS STEP 1:  This is the first step in translating a plugin."
	echo "The next step is './updatepos.sh.'"
	echo ""
	echo "Obtains the WordPress i18n / makepot package if needed."
	echo ""
	echo "For more information on translating your plugin, see"
	echo "http://codex.wordpress.org/I18n_for_WordPress_Developers"
	echo ""
	echo "Author: Daniel Convissor <danielc@analysisandsolutions.com>"
	echo "License: http://www.gnu.org/licenses/gpl-2.0.html"
	echo "http://wordpress.org/extend/plugins/oop-plugin-template-solution/"
	exit 1
fi

cd "`dirname "$0"`/../.."

if [ ! -d makepot ] ; then
	svn checkout http://i18n.svn.wordpress.org/tools/trunk makepot
fi

cd makepot
svn up

php -d 'error_reporting=E_ALL^E_STRICT' \
	makepot.php wp-plugin \
	../oop-plugin-template-solution \
	../oop-plugin-template-solution/languages/oop-plugin-template-solution.pot
