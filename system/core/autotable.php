<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Autotable {

  function set_autotable(){
		$db = & load_class('DB');
		$database_type = '';
		$crypt = & load_class('Crypt');
		$tabel = $db->get_table();
		$options = include('application/config/database.php');
		
		if (isset($options['database_type'])){
			$database_type = strtolower($options['database_type']);
		}

		if (!in_array('roles', $tabel)) {
			
			if($database_type === 'pgsql') {
				$db->exec("CREATE TABLE roles (
					id_role SERIAL PRIMARY KEY,
					role_name varchar(50) NOT NULL,
					description varchar(255) NOT NULL,
					permission text
				)");
			} else {
				$db->exec("CREATE TABLE `roles` (
					`id_role` int(10) NOT NULL AUTO_INCREMENT,
					`role_name` varchar(50) NOT NULL,
					`description` varchar(255) NOT NULL,
					`permission` text,
					PRIMARY KEY (`id_role`)
				)");
			}

			$data_role['id_role'] = '1';
			$data_role['role_name'] = 'Administrator';
			$data_role['description'] = 'Memiliki Hak Akses Tertinggi Dalam Aplikasi';
			$data_role['permission'] = '';
			$db->insert('roles', $data_role);

			$data_role['id_role'] = '2';
			$data_role['role_name'] = 'Guest';
			$data_role['description'] = 'Pengunjung Website';
			$data_role['permission'] = '';
			$db->insert('roles', $data_role);
    }
		
		if (!in_array('users', $tabel)) {
			if($database_type === 'pgsql') {
				$db->exec("CREATE TABLE users (
					id_user SERIAL PRIMARY KEY,
					username varchar(100) NOT NULL,
					name varchar(255) NOT NULL,
					password text NOT NULL,
					id_role int NOT NULL,
					id_type smallint,
					id_external bigint
				)");
			} else {
				$db->exec("CREATE TABLE `users` (
					`id_user` int(10) NOT NULL AUTO_INCREMENT,
					`username` varchar(100) NOT NULL,
					`name` varchar(255) NOT NULL,
					`password` text NOT NULL,
					`id_role` int(10) NOT NULL,
					`id_type` tinyint(1),
					`id_external` bigint(20),
					PRIMARY KEY (`id_user`)
				)");
			}
				
			$data_user['id_user'] = '1';
			$data_user['username'] = 'Admin';
			$data_user['name'] = 'Admin';
			$data_user['password'] = password_hash("Admin",1);
			$data_user['id_role'] = '1';
			$db->insert('users', $data_user);

			$data_user['id_user'] = '2';
			$data_user['username'] = 'Guest';
			$data_user['name'] = 'Guest';
			$data_user['password'] = password_hash("Guest",1);
			$data_user['id_role'] = '2';
			$db->insert('users', $data_user);
    }

		if (!in_array('short_link', $tabel)){
			if ($database_type === 'pgsql') {
				$db->exec("CREATE TABLE users (
					id_link SERIAL PRIMARY KEY,
					link varchar(100) NOT NULL,
					short_link varchar(50) NOT NULL
				)");
			} else {
				$db->exec("CREATE TABLE `short_link` (
					`id_link` int(10) NOT NULL AUTO_INCREMENT,
					`link` varchar(100) NOT NULL,
					`short_link` varchar(50) NOT NULL,
					PRIMARY KEY (`id_link`),
					UNIQUE KEY `short_link` (`short_link`),
					UNIQUE KEY `link` (`link`)
				)");
			}
		}
	}        

}

?>