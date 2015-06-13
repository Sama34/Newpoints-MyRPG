<?php

/***************************************************************************
 *
 *	Newpoints MyRPG plugin (/inc/plugins/newpoints/newpoints_myrpg.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2015 Omar Gonzalez
 *
 *	Website: http://omarg.me
 *
 *	Integrates a powerful RPG system to your Newpoints installation.
 *
 ***************************************************************************

****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Disallow direct access to this file for security reasons
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Add the hooks we are going to use.
if(defined('IN_ADMINCP'))
{
	$plugins->add_hook('newpoints_rebuild_templates', 'newpoints_myrpg_rebuild_templates');

	$plugins->add_hook('admin_newpoints_action_handler', 'newpoints_myrpg_admin_newpoints_action_handler');
	/*$plugins->add_hook('newpoints_admin_newpoints_action_handler', 'newpoints_myrpg_admin_newpoints_action_handler');
	$plugins->add_hook('newpoints_admin_newpoints_permissions', 'newpoints_myrpg_admin_newpoints_permissions');
	$plugins->add_hook('newpoints_admin_maintenance_edituser_form', 'newpoints_myrpg_admin_maintenance_edituser_form');
	$plugins->add_hook('newpoints_admin_maintenance_edituser_commit', 'newpoints_myrpg_admin_maintenance_edituser_commit');*/
}
else
{
	$plugins->add_hook('global_start', 'newpoints_myrpg_global_start');
	$plugins->add_hook('member_profile_end', 'newpoints_myrpg_member_profile_end');

	if(THIS_SCRIPT == 'myrpg.php')
	{
		global $templatelist;

		if(isset($templatelist))
		{
			$templatelist .= ',';
		}
		else
		{
			$templatelist = '';
		}
		$templatelist .= '';
	}
}

// Plugin API
function newpoints_myrpg_info()
{
	global $myrpg, $lang;
	$myrpg->lang_load();

	return array(
		'name'			=> 'Newpoints MyRPG',
		'description'	=> $lang->newpoints_myrpg_desc,
		'website'		=> 'http://omarg.me',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://omarg.me',
		'version'		=> '1.0',
		'versioncode'	=> 1000,
		'compatibility'	=> '2*'
	);
}

// _activate() routine
function newpoints_myrpg_activate()
{
	global $myrpg, $db, $lang, $cache;
	$myrpg->lang_load();

	// rebuild templates
	newpoints_rebuild_templates();

	// Now we can insert our settings
	newpoints_add_settings('newpoints_myrpg', array(
			'foo'	=> array(
				'title'			=> $lang->setting_newpoints_myrpg_foo,
				'description'	=> $lang->setting_newpoints_myrpg_foo_desc,
				'type'			=> 'numeric',
				'value'			=> 30
			),
	));

	// Add the button variable
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	//find_replace_templatesets('showthread', '#'.preg_quote('{$newreply}').'#', '{$newpoints_myrpg}{$newreply}');

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');
	if(!$plugins)
	{
		$plugins = array();
	}

	$info = newpoints_myrpg_info();

	if(!isset($plugins['newpoints_myrpg']))
	{
		$plugins['newpoints_myrpg'] = $info['versioncode'];
	}

	/*~*~* RUN UPDATES START *~*~*/
	if($plugins['newpoints_myrpg'] <= 1000)
	{

	}
	/*~*~* RUN UPDATES END *~*~*/

	$plugins['newpoints_myrpg'] = $info['versioncode'];
	$cache->update('ougc_plugins', $plugins);
}

// _deactivate
function newpoints_myrpg_deactivate()
{
	// Remove the button variable
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	//find_replace_templatesets('showthread', '#'.preg_quote('{$newpoints_myrpg}').'#', '',0);
}

// _install
function newpoints_myrpg_install()
{
	global $db;

	switch($db->type)
	{
		case 'pgsql':
		case 'sqlite':
		default:
			$collation = $db->build_create_table_collation();
			$db->write_query("CREATE TABLE `".TABLE_PREFIX."newpoints_myrpg_characters_categories` (
				`cid` int UNSIGNED NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL DEFAULT '',
				`disporder` smallint NOT NULL DEFAULT '0',
				PRIMARY KEY (`cid`)
			) ENGINE=MyISAM{$collation};");
			$db->write_query("CREATE TABLE `".TABLE_PREFIX."newpoints_myrpg_characters_characters` (
				`chid` int UNSIGNED NOT NULL AUTO_INCREMENT,
				`cid` int UNSIGNED NOT NULL DEFAULT '0',
				`name` varchar(255) NOT NULL DEFAULT '',
				`disporder` smallint NOT NULL DEFAULT '0',
				PRIMARY KEY (`chid`)
			) ENGINE=MyISAM{$collation};");
			$db->write_query("CREATE TABLE `".TABLE_PREFIX."newpoints_myrpg_characters_levels` (
				`lid` int UNSIGNED NOT NULL AUTO_INCREMENT,
				`chid` int UNSIGNED NOT NULL DEFAULT '0',
				`level` smallint(5) NOT NULL DEFAULT '0',
				`price` DECIMAL(16,2) NOT NULL DEFAULT '0',
				PRIMARY KEY (`lid`),
				UNIQUE INDEX (chid,level)
			) ENGINE=MyISAM{$collation};");
	}

	if(!$db->field_exists('newpoints_myrpg_characters_character', 'users'))
	{
		$db->add_column('users', 'newpoints_myrpg_characters_character', "int NOT NULL DEFAULT '0'");
	}
	if(!$db->field_exists('newpoints_myrpg_characters_level', 'users'))
	{
		$db->add_column('users', 'newpoints_myrpg_characters_level', "int NOT NULL DEFAULT '0'");
	}

	/*if(!$db->field_exists('newpoints_myrpg_user_hp', 'users'))
	{
		$db->add_column('users', 'newpoints_myrpg_user_hp', "int NOT NULL DEFAULT '0'");
	}
	if(!$db->field_exists('newpoints_myrpg_user_attack', 'users'))
	{
		$db->add_column('users', 'newpoints_myrpg_user_attack', "int NOT NULL DEFAULT '0'");
	}
	if(!$db->field_exists('newpoints_myrpg_user_defense', 'users'))
	{
		$db->add_column('users', 'newpoints_myrpg_user_defense', "int NOT NULL DEFAULT '0'");
	}
	if(!$db->field_exists('newpoints_myrpg_user_spattack', 'users'))
	{
		$db->add_column('users', 'newpoints_myrpg_user_spattack', "int NOT NULL DEFAULT '0'");
	}
	if(!$db->field_exists('newpoints_myrpg_user_spdefense', 'users'))
	{
		$db->add_column('users', 'newpoints_myrpg_user_spdefense', "int NOT NULL DEFAULT '0'");
	}
	if(!$db->field_exists('newpoints_myrpg_user_speed', 'users'))
	{
		$db->add_column('users', 'newpoints_myrpg_user_speed', "int NOT NULL DEFAULT '0'");
	}
	if(!$db->field_exists('newpoints_myrpg_user_lucky', 'users'))
	{
		$db->add_column('users', 'newpoints_myrpg_user_lucky', "int NOT NULL DEFAULT '0'");
	}*/
}

// _uninstall
function newpoints_myrpg_uninstall()
{
	global $db, $cache;

	// Remove the plugin settings.
	newpoints_remove_settings("'newpoints_myrpg_interval', 'newpoints_myrpg_forums', 'newpoints_myrpg_groups', 'newpoints_myrpg_points'");

	// Remove the plugin template.
	newpoints_myrpg_rebuild_templates($template_list);
	newpoints_remove_templates("'newpoints_".implode("','newpoints_", array_keys($template_list))."'");

	// Remove the plugin columns, if any...
	$db->drop_table('newpoints_myrpg_characters_categories');
	$db->drop_table('newpoints_myrpg_characters_characters');
	$db->drop_table('newpoints_myrpg_characters_levels');

	if($db->field_exists('newpoints_myrpg_characters_character', 'users'))
	{
		$db->drop_column('users', 'newpoints_myrpg_characters_character');
	}
	if($db->field_exists('newpoints_myrpg_characters_level', 'users'))
	{
		$db->drop_column('users', 'newpoints_myrpg_characters_level');
	}

	// Clean any logs from this plugin.
	newpoints_remove_log(array('myrpg'));

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['newpoints_myrpg']))
	{
		unset($plugins['newpoints_myrpg']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$cache->delete('ougc_plugins');
	}
}

// _is_insalled
function newpoints_myrpg_is_installed()
{
	global $db;

	return $db->table_exists('newpoints_myrpg_characters_categories');
}

// Insert our template
function newpoints_myrpg_rebuild_templates(&$tmpls)
{
	$tmpls['newpoints_myrpg_global_notification'] = '<div class="pm_alert"><a href="{$mybb->settings[\'bburl\']}/{$url}" title="{$message}">{$message}</a></div>';
}

// Custom ACP menu
function newpoints_myrpg_admin_newpoints_action_handler(&$actions)
{
	global $myrpg, $lang, $plugins, $page;
	$myrpg->lang_load();

	// MyRPG
	$sub_menu = array(
		10	=> array(
			'id'	=> 'myrpg_characters',
			'title'	=> $lang->newpoints_myrpg_characters,
			'link'	=> 'index.php?module=newpoints-myrpg&submod=characters',
		),
	);

	$sub_menu = $plugins->run_hooks('admin_newpoints_myrpg_menu', $sub_menu);

	$sidebar = new SidebarItem($lang->newpoints_myrpg);
	$sidebar->add_menu_items($sub_menu, $page->active_action);

	$page->sidebar .= $sidebar->get_markup();

	// Actions
	$actions['myrpg'] = array(
		'active'	=> 'myrpg_characters',
		'file'		=> 'myrpg_characters.php',
	);
}

// Hook: global_start
function newpoints_myrpg_global_start()
{
	global $mybb, $newpoints_myrpg, $lang, $templates;

	if(!$mybb->user['uid'])
	{
		return;
	}

	if(!$mybb->user['newpoints_myrpg_characters_character'] || !$mybb->user['newpoints_myrpg_characters_level'])
	{
		$url = 'newpoints.php?action=myrpg&amp;characters=home';
		$message = $lang->newpoints_myrpg_global_notification_new;
		$newpoints_myrpg = eval($templates->render('newpoints_myrpg_global_notification'));
	}
}

// Hook: member_profile_end
function newpoints_myrpg_member_profile_end()
{
	global $mybb, $memprofile, $newpoints_myrpg, $myrpg;

	if(!$memprofile['newpoints_myrpg_characters_character'] || !$memprofile['newpoints_myrpg_characters_level'])
	{
		return;
	}

	$character = $myrpg->characters_get($memprofile['newpoints_myrpg_characters_character']);
	$category = $myrpg->characters_category_get($character['cid']);
	$level = $myrpg->characters_level_get($memprofile['newpoints_myrpg_characters_level']);

	if($category && $character && $level)
	{
		// show
	}
}

// Custom class object
class Newpoints_MyRPG
{
	function lang_load()
	{
		global $lang;

		isset($lang->newpoints_myrpg) or newpoints_lang_load('newpoints_myrpg');
	}

	function admin_action_set($params)
	{
		global $PL;
		$PL or require_once PLUGINLIBRARY;

		if(!is_array($params))
		{
			$params = explode('=', $params);
			$params = array($params[0]=>$params[1]);
		}

		$this->admin_action = (string)$PL->url_append('index.php?module=newpoints-myrpg', (array)$params);
	}

	function admin_action_get($params=null)
	{
		if($params !== null)
		{
			global $PL;
			$PL or require_once PLUGINLIBRARY;

			if(!is_array($params))
			{
				$params = explode('=', $params);
				$params = array($params[0]=>$params[1]);
			}

			return (string)$PL->url_append($this->admin_action, (array)$params);
		}

		return (string)$this->admin_action;
	}

	function errors_set($key)
	{
		$this->errors[$key] = $key;
	}

	function errors_get()
	{
		global $lang;
		$this->lang_load();

		$errors = array();

		foreach($this->errors as $error)
		{
			$lang_var = 'newpoints_errors_'.$error;
			if(!empty($lang->{$lang_var}))
			{
				$errors[$error] = $lang->{$lang_var};
			}
			else
			{
				$errors['unknown'] = 'unknown_error';
			}
		}

		return $errors;
	}

	function admin_redirect($params=null, $message='', $error=false)
	{
		if($message)
		{
			flash_message($message, $error ? 'error' : 'success');
		}

		admin_redirect($this->admin_action_get($params));
		exit;
	}

	function characters_set($character)
	{
		$this->character = array(
			'name'		=> (string)$character['name'],
			'disporder'	=> (int)$character['disporder'],
			'cid'	=> (int)$character['cid'],
		);
	}

	function characters_validate()
	{
		if(!$this->character['name'] || my_strlen($this->character['name']) > 255)
		{
			$this->errors_set('invalid_name');
			return false;
		}

		if($this->character['disporder'] < 1)
		{
			$this->errors_set('invalid_disporder');
			return false;
		}

		if(!$this->characters_category_get($this->character['cid']))
		{
			$this->errors_set('invalid_category');
			return false;
		}

		return true;
	}

	function characters_insert($data, $update=false, $chid=0)
	{
		global $db;

		$new_data = array();

		if(isset($data['name']))
		{
			$new_data['name'] = $db->escape_string($data['name']);
		}
		if(isset($data['disporder']))
		{
			$new_data['disporder'] = (int)$data['disporder'];
		}
		if(isset($data['cid']))
		{
			$new_data['cid'] = (int)$data['cid'];
		}

		if(!$new_data)
		{
			return false;
		}

		if($update)
		{
			$chid = (int)$chid;
			$db->update_query('newpoints_myrpg_characters_characters', $new_data, "chid='{$chid}'");
		}
		else
		{
			$db->insert_query('newpoints_myrpg_characters_characters', $new_data);
		}

		return true;
	}

	function characters_update($data, $chid)
	{
		return $this->characters_insert($data, true, $chid);
	}

	function characters_get($chid)
	{
		global $db;

		$chid = (int)$chid;

		$query = $db->simple_select('newpoints_myrpg_characters_characters', '*', "chid='{$chid}'");

		return $db->fetch_array($query);
	}

	function characters_delete($chid)
	{
		global $db;

		$chid = (int)$chid;

		return $db->delete_query('newpoints_myrpg_characters_characters', "chid='{$chid}'");
	}

	function characters_category_set($category)
	{
		$this->category = array(
			'name'		=> (string)$category['name'],
			'disporder'	=> (int)$category['disporder'],
		);
	}

	function characters_category_validate()
	{
		if(!$this->category['name'] || my_strlen($this->category['name']) > 255)
		{
			$this->errors_set('invalid_name');
			return false;
		}

		if($this->category['disporder'] < 1)
		{
			$this->errors_set('invalid_disporder');
			return false;
		}

		return true;
	}

	function characters_category_insert($data, $update=false, $cid=0)
	{
		global $db;

		$new_data = array();

		if(isset($data['name']))
		{
			$new_data['name'] = $db->escape_string($data['name']);
		}
		if(isset($data['disporder']))
		{
			$new_data['disporder'] = (int)$data['disporder'];
		}

		if(!$new_data)
		{
			return false;
		}

		if($update)
		{
			$cid = (int)$cid;
			$db->update_query('newpoints_myrpg_characters_categories', $new_data, "cid='{$cid}'");
		}
		else
		{
			$db->insert_query('newpoints_myrpg_characters_categories', $new_data);
		}

		return true;
	}

	function characters_category_update($data, $cid)
	{
		return $this->characters_category_insert($data, true, $cid);
	}

	function characters_category_get($cid)
	{
		global $db;

		$cid = (int)$cid;

		$query = $db->simple_select('newpoints_myrpg_characters_categories', '*', "cid='{$cid}'");

		return $db->fetch_array($query);
	}

	function characters_category_delete($cid)
	{
		global $db;

		$cid = (int)$cid;

		return $db->delete_query('newpoints_myrpg_characters_categories', "cid='{$cid}'");
	}

	function characters_level_set($level)
	{
		$this->level = array(
			'level'	=> (int)$level['level'],
			'price'	=> (float)$level['price'],
			'chid'	=> (int)$level['chid'],
		);
	}

	function characters_level_validate()
	{
		if($this->level['level'] < 1)
		{
			$this->errors_set('invalid_level');
			return false;
		}
		else
		{
			global $db;

			$chid = (int)$this->level['chid'];
			$level = (int)$this->level['level'];

			$query = $db->simple_select('newpoints_myrpg_characters_levels', 'lid', "chid='{$chid}' AND level='{$level}'");
			if($db->fetch_field($query, 'lid'))
			{
				$this->errors_set('invalid_level');
				return false;
			}
		}

		if($this->level['price'] < 0)
		{
			$this->errors_set('invalid_price');
			return false;
		}

		return true;
	}

	function characters_level_insert($data, $update=false, $lid=0)
	{
		global $db;

		$new_data = array();

		if(isset($data['level']))
		{
			$new_data['level'] = (int)$data['level'];
		}
		if(isset($data['price']))
		{
			$new_data['price'] = (float)$data['price'];
		}
		if(isset($data['chid']))
		{
			$new_data['chid'] = (int)$data['chid'];
		}

		if(!$new_data)
		{
			return false;
		}

		if($update)
		{
			$lid = (int)$lid;
			$db->update_query('newpoints_myrpg_characters_levels', $new_data, "lid='{$lid}'");
		}
		else
		{
			$db->insert_query('newpoints_myrpg_characters_levels', $new_data);
		}

		return true;
	}

	function characters_level_update($data, $lid)
	{
		return $this->characters_level_insert($data, true, $lid);
	}

	function characters_level_get($lid)
	{
		global $db;

		$lid = (int)$lid;

		$query = $db->simple_select('newpoints_myrpg_characters_levels', '*', "lid='{$lid}'");

		return $db->fetch_array($query);
	}

	function characters_level_delete($lid)
	{
		global $db;

		$lid = (int)$lid;

		return $db->delete_query('newpoints_myrpg_characters_levels', "lid='{$lid}'");
	}
}

$GLOBALS['myrpg'] = new Newpoints_MyRPG;