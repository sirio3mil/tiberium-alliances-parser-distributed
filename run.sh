rm /var/log/taparser
killall -9 php
nohup php src/Sparser.php > /var/log/taparser 2>&1&
nohup php src/Wparser.php > /dev/null 2>&1&
nohup php src/Wparser.php > /dev/null 2>&1&
nohup php src/Wparser.php > /dev/null 2>&1&
nohup php src/Wparser.php > /dev/null 2>&1&

nohup php src/Suploader.php > /dev/null 2>&1&
nohup php src/Wuploader.php > /dev/null 2>&1&
nohup php src/Wuploader.php > /dev/null 2>&1&
