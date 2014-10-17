<?php
	namespace XDUtils;

	/**
	 * 	Database utils based on Zebra_Datbase
	 *	@see http://stefangabos.ro/php-libraries/zebra-database/
	 *
	 * 	@author XIDA
	 */
	class Database extends \Zebra_Database {

		/**
		 *	The array key for the sql returned by SHOW CREATE TABLE
		 */
		const CREATE_TABLE_KEY = 'Create Table';

		/**
		 *	Get the create sql for the current table
		 *
		 *	@param	string			$table
		 *
		 *	@return	string			The sql query
		 */
		private function getCreateSqlForTable($table) {
			// get the create table sql
			$this->query('SHOW CREATE TABLE `' . $table . '`');
			$result = $this->fetch_assoc();

			// return the sql
			return $result[self::CREATE_TABLE_KEY] . ';';
		}

		/**
		 *	Get the full sql code for a table with data
		 *
		 *	@param	string			$table
		 *
		 *	@return string			The sql query
		 */
		private function getFullSqlForTable($table) {
			// drop table first
			$sql = 'DROP TABLE IF EXISTS ' . $table . ';' . PHP_EOL;

			// create table
			$sql .= $this->getCreateSqlForTable($table) . PHP_EOL . PHP_EOL;

			// insert values
			$sql .= $this->getInsertSqlForTable($table) . PHP_EOL . PHP_EOL;
			return $sql;
		}

		/**
		 *	Get insert sql for data of a table
		 *
		 *	@param	string			$table
		 *
		 *	@return string			The sql query
		 */
		private function getInsertSqlForTable($table) {
			// insert values
			$sql = 'INSERT INTO `' . $table . '` VALUES' . PHP_EOL;

			// get the data from table
			$this->select('*', $table);
			$i = 0;
			while($row = $this->fetch_assoc()) {
				$i++;
				//L ogger::notice('[DB] fetching value ' . $i . '/' . $this->returned_rows);
				$sql .= '(';
				foreach($row as $value) {
					$sql .= is_int($value) ? $value : "'" . $this->escape($value) . "',";
				}
				// remove last comma and add close bracket
				$sql = rtrim($sql, ',') . '),' . PHP_EOL;
			}
			// replace last comma and PHP_EOL with semicolon
			return substr_replace($sql, ';', -3) . PHP_EOL;
		}

		/**
		 *	Backup sql for table to a file
		 *
		 *	@param	string			$file
		 *	@param	string			$table
		 *
		 *	@return \XDUtils\Database		return $this for chaining
		 */
		public function backupTable($file, $table) {
			$sql = $this->getFullSqlForTable($table);
			File::saveStringToFile($sql, $file);
			return $this;
		}

		/**
		 *	Backup sql for the whole database to a file
		 *
		 *	@param	string			$file
		 *
		 *	@return \XDUtils\Database		return $this for chaining
		 */
		public function backupDatabase($file) {
			$sql	= '';
			$tables = $this->get_tables();
			// cycle through tables
			$i = 0;
			foreach($tables as $table) {
				$i++;
				Logger::info('Backup table ' . $i . '/' . count($tables) . ' ('. $table . ')');
				$sql .= $this->getFullSqlForTable($table);
			}
			File::saveStringToFile($sql, $file);
			return $this;
		}
	}
?>