#! /bin/bash
# Run from your git working directory.
#
# Taken from: https://github.com/thenbrent/multisite-user-management/blob/master/deploy.sh
# A modification of Dean Clatworthy's deploy script as found here: https://github.com/deanc/wordpress-plugin-git-svn
# The difference is that this script lives in the plugin's git repo & doesn't require an existing SVN repo.

# main config
PLUGINSLUG="broadly"
CURRENTDIR=`pwd`
MAINFILE="broadly.php" # this should be the name of your main php file in the wordpress plugin

# git config
GITPATH="$CURRENTDIR/" # this file should be in the base of your git repository

# svn config
SVNPATH="/tmp/$PLUGINSLUG" # path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNURL="http://plugins.svn.wordpress.org/broadly/" # Remote SVN repo on wordpress.org
SVNUSER="broadly" # your svn username


# Let's begin...
echo ".........................................."
echo
echo "Preparing to deploy wordpress plugin"
echo
echo ".........................................."
echo

# Needs to run in directory with plugin files
if [ ! -f "$MAINFILE" ]
then
	echo "Plugin file $MAINFILE not in current directory. Exiting..."
	exit 1;
fi

# Check if subversion is installed before getting all worked up
if ! which svn >/dev/null; then
	echo "You'll need to install subversion before proceeding. Exiting...";
	exit 1;
fi

# Check version in readme.txt is the same as plugin file after translating both to unix line breaks to work around grep's failure to identify mac line breaks
NEWVERSION1=`grep "^Stable tag:" $GITPATH/readme.txt | awk -F' ' '{print $NF}'`
echo "readme.txt version: $NEWVERSION1"
NEWVERSION2=`grep "^Version:" $GITPATH/$MAINFILE | awk -F' ' '{print $NF}'`
echo "$MAINFILE version: $NEWVERSION2"

if [ "$NEWVERSION1" != "$NEWVERSION2" ];
	then
		echo "Version in readme.txt & $MAINFILE don't match. Exiting....";
		exit 1;
	else
		echo "Versions match in readme.txt and $MAINFILE. Let's proceed..."
fi

if git show-ref --tags --quiet --verify -- "refs/tags/$NEWVERSION1"
	then
		echo "Version $NEWVERSION1 already exists as git tag. Exiting...";
		exit 1;
	else
		echo "Git tag does not yet exist. Let's proceed..."
fi

# Do SVN checkout and clear to make room for new files
echo "Creating local copy of SVN repo ..."
svn co $SVNURL $SVNPATH

if [ -d "$SVNPATH/tags/$NEWVERSION1" ]
	then
	  echo "Version $NEWVERSION1 already exists as SVN tag. Exiting...";
	  exit 1;
	else
		echo "SVN tag does not yet exist. Let's proceed..."
fi

echo "Clearing svn trunk so we can overwrite it"
svn rm $SVNPATH/trunk/*

# Check changes, git commit if needed
echo -e "Enter a commit message for this new version: \c"
read COMMITMSG
if [ -n "$(git status --porcelain)" ];
	then
		git commit -am "$COMMITMSG"
		echo "Push new version to git"
		git push origin master
fi

exit

echo "Exporting the HEAD of master from git to the trunk of SVN"
git checkout-index -a -f --prefix=$SVNPATH/trunk/

# Confirm we got the plugin file to prevent blank releases
if [ -f "$SVNPATH/trunk/$MAINFILE" ]
then
	echo "Check out HEAD and main plugin file completed successfully"
else
	echo "Plugin file $MAINFILE missing from git checkout. Exiting..."
	exit 1;
fi

echo "Ignoring github specific files and deployment script"
svn propset svn:ignore "deploy.sh
DEVELOPMENT.md
docker-compose.yml
.git
.gitignore" "$SVNPATH/trunk/"

echo "Changing directory to SVN and committing to trunk"
cd $SVNPATH/trunk/
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add
svn commit --username=$SVNUSER -m "$COMMITMSG"

echo "Creating new SVN tag & committing it"
cd $SVNPATH
svn copy trunk/* tags/$NEWVERSION1/
cd $SVNPATH/tags/$NEWVERSION1
svn commit --username=$SVNUSER -m "Tagging version $NEWVERSION1"

echo "Tagging new version in git"
cd $GITPATH
git tag -a "$NEWVERSION1" -m "Tagging version $NEWVERSION1"
git push origin master --tags

echo "Removing temporary directory $SVNPATH"
rm -fr $SVNPATH/

echo "*** FIN ***"
