#!/usr/bin/php -f
<?php

/**
 * PICKLES Scaffolding Generator (verb: to pickle)
 *
 * This is the file that you include on the page you're instantiating the
 * controller from (typically index.php).  The path to the PICKLES code base
 * is established as well as the path that Smarty will use to store the
 * compiled pages.
 *
 * PICKLES is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * PICKLES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with PICKLES.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @author    Joshua John Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007, 2008, 2009 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 * @usage     ./pickle /path/to/new/site
 */

$error = null;

$path = isset($argv[1]) ? $argv[1] . (substr($path, -1, 1) != '/' ? '/' : '') : './';

if (!file_exists($path)) {
	if (!mkdir($path, 0777, true)) {
		exit("Error: Unable to create directory ({$path})\n");
	}
}

if (!is_dir($path)) {
	exit("Error: The path specified ({$path}) is not a directory\n");
}
else if (!is_writable($path)) {
	exit("Error: The path specified ({$path}) is not writable\n");
}
else {
	$pickles_path   = str_replace('scripts', '', dirname(__FILE__));

	foreach (array('public', 'models', 'templates') as $directory) {
		$directory = $path . $directory;

		if (!file_exists($directory)) {
			mkdir($directory);
		}

		if (!is_writable($directory)) {
			exit("Error: The path specified ({$directory}) is not writable\n");
		}
	}

	// config.xml
	$config = <<<XML
<config>
	<database>
		<hostname>localhost</hostname>
		<username></username>
		<password></password>
		<database></database>
	</database>
	<models>
		<default>home</default>
	</models>
</config>
XML;
	file_put_contents($path . 'config.xml', $config);

	// public/.htaccess
	$htaccess = <<<CONF
# Alias the static libraries
Alias /static/ {$pickles_path}static/

# Set the PHP include path
php_value include_path \".:{$pickles_path}\"

# Prevent session IDs from appearing
php_value session.use_only_cookies 1
php_value session.use_trans_sid 0

# Sets up the mod_rewrite engine
RewriteEngine on

# Sets the base path (document root)
RewriteBase /

# Strips the trailing slash
RewriteRule ^(.+)/$ $1 [R]

# Rewrite Rules for the PICKLES Quaternity
RewriteRule ^([a-z-/]+)$                        index.php?model=$1                  [NC,QSA]
RewriteRule ^([a-z-/]+)/([0-9]+)$               index.php?model=$1&id=$2            [NC,QSA]
RewriteRule ^([a-z-/]+)/page/([0-9]+)$          index.php?model=$1&page=$2          [NC,QSA]
RewriteRule ^([a-z-/]+)/([0-9/]{10})/([a-z-]+)$ index.php?model=$1&date=$2&title=$3 [NC,QSA]

# Blocks access to .htaccess
<Files .htaccess>
	order allow,deny
	deny from all
</Files>
CONF;
	file_put_contents($path . 'public/.htaccess', $htaccess);

	// public/index.php
	$index = <<<PHP
<?php

ini_set('include_path', ini_get('include_path') . ':{$pickles_path}');

require_once 'pickles.php';

new Controller();

?>
PHP;
	file_put_contents($path . 'public/index.php', $index);

	// models/home.php
	$home = <<<PHP
<?php

class home extends Model {

	// The follow are set to the default values, so they are optional
	protected \$authorization = false;
	protected \$display       = 'Smarty'; 
	protected \$session       = false;

	public function __default() {

		// \$this->db->getField('SELECT ...');
		// \$this->db->getRow('SELECT ...');
		// \$this->db->getArray('SELECT ...');

		\$this->message = "You have successfully set up a site <a href='http://phpwithpickles.org/'>with PICKLES!</a>";
	}
}

?>
PHP;
	file_put_contents($path . 'models/home.php', $home);

	// templates/index.tpl
	$index = <<<HTML
<html>
	<head>
		<title>Congratulations</title>
	</head>
	<body>
		{include file="\$template"}
	</body>
</html>
HTML;
	file_put_contents($path . 'templates/index.tpl', $index);

	// templates/home.tpl
	$home = <<<HTML
<h1>Congratulations!</h1>
<h2>{\$message}</h2>
HTML;
	file_put_contents($path . 'templates/home.tpl', $home);
}

?>