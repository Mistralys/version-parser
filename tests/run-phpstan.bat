@echo off

cls

set AnalysisLevel=9
set OutputFile=phpstan/output.txt
set ConfigFile=./phpstan/config.neon

echo -------------------------------------------------------
echo RUNNING PHPSTAN @ LEVEL %AnalysisLevel%
echo -------------------------------------------------------

echo.

call ../vendor/bin/phpstan analyse -c %ConfigFile% -l %AnalysisLevel% > %OutputFile%

echo.
echo Saved to %OutputFile%.
echo.

start "" "%OutputFile%"
