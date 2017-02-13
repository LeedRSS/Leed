<?php
$userConfsAvailable = array(
    'articleDisplayAuthor',
    'articleDisplayDate',
    'articleDisplayFolderSort',
    'articleDisplayHomeSort',
    'articleDisplayLink',
    'articleDisplayMode',
    'articlePerPages',
    'displayOnlyUnreadFeedFolder',
    'feedMaxEvents',
    'language',
    'optionFeedIsVerbose',
    'paginationScale',
    'synchronisationEnableCache',
    'synchronisationForceFeed',
    'synchronisationType',
    'theme'
);
$conn = new MysqlEntity();
$confs = $conn->customQuery('SELECT `key`, `value` FROM `'.MYSQL_PREFIX.'configuration`;');
$userConfs = array();
while($row = $confs->fetch_assoc()) {
    if(in_array($row['key'], $userConfsAvailable)) {
        $userConfs[$row['key']] = $row['value'];
    }
}
$confsJson = json_encode($userConfs);
$updateConfs = $conn->customQuery('ALTER TABLE `'.MYSQL_PREFIX.'user` ADD `conf` MEDIUMTEXT NOT NULL;');
$query = 'UPDATE `'.MYSQL_PREFIX.'user` SET `conf`=\''.$confsJson.'\' WHERE `id`=1;';
$updateConfs = $conn->customQuery($query);


$deleteUselesssConfsQuery = 'DELETE from `'.MYSQL_PREFIX.'configuration` WHERE `key` IN ("'.implode('","', $userConfsAvailable).'");';
$removeUselessConfs = $conn->customQuery($deleteUselesssConfsQuery);

?>
