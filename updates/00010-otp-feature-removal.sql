--######################################################################################################
--#####
--#####     Leed Database Update
--#####			Date : 10/05/2023
--#####			Version Leed : v1.12.0
--#####
--##### 		Feature(s) :
--#####			- OTP feature removal
--#####
--######################################################################################################

DELETE FROM `##MYSQL_PREFIX##configuration` WHERE `key`='otpEnabled';
ALTER TABLE `##MYSQL_PREFIX##user` DROP COLUMN `otpSecret`;
