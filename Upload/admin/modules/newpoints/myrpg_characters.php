<?php

/*
 * Newpoints User Characters plugin
 * Author: Omar Gonzalez.
 * Copyright: © 2012 Omar Gonzalez, All Rights Reserved
 * 
 * Website: http://www.udezain.com.ar
 *
 * Allow users to user characters using the Newpoints system.
 *
************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// Load language
$myrpg->lang_load();

$myrpg->admin_action_set('submod=characters');

$page->add_breadcrumb_item($lang->newpoints_myrpg, $myrpg->admin_action_get());

$sub_tabs['newpoints_myrpg_characters_categories'] = array(
	'title'			=> $lang->newpoints_myrpg_categories,
	'link'			=> $myrpg->admin_action_get(),
	'description'	=> $lang->newpoints_myrpg_characters_categories_desc
);

if($mybb->get_input('view') == 'category' || $mybb->get_input('view') == 'levels')
{
	$sub_tabs['newpoints_myrpg_characters_characters'] = array(
		'title'			=> $lang->newpoints_myrpg_characters,
		'link'			=> $myrpg->admin_action_get(array('view'=>'category','cid'=>$mybb->get_input('cid', 1))),
		'description'	=> $lang->newpoints_myrpg_characters_desc
	);
}

if($mybb->get_input('view') == 'category')
{
	if(!($category = $myrpg->characters_category_get($mybb->get_input('cid', 1))))
	{
		$myrpg->admin_redirect(null, $lang->newpoints_errors_invalid_category, true);
	}

	$category['cid'] = (int)$category['cid'];

	$page->add_breadcrumb_item($lang->newpoints_myrpg_characters, $myrpg->admin_action_get());
	$page->add_breadcrumb_item(htmlspecialchars_uni($category['name']), $myrpg->admin_action_get(array('view'=>'category','cid'=>$mybb->get_input('cid', 1))));

	// Page tabs
	$sub_tabs['newpoints_myrpg_characters_characters_add'] = array(
		'title'			=> $lang->newpoints_myrpg_add,
		'link'			=> $myrpg->admin_action_get(array('view'=>'category','action'=>'add','cid'=>$mybb->get_input('cid', 1))),
		'description'	=> $lang->newpoints_myrpg_characters_add_desc
	);
	if($mybb->get_input('action') == 'edit')
	{
		$sub_tabs['newpoints_myrpg_characters_characters_edit'] = array(
			'title'			=> $lang->newpoints_myrpg_edit,
			'link'			=> $myrpg->admin_action_get(array('view'=>'category','action'=>'edit','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1))),
			'description'	=> $lang->newpoints_myrpg_characters_edit_desc
		);
	}

	if($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit')
	{
		$add = ($mybb->get_input('action') == 'add' ? true : false);

		if($add)
		{
			$page->add_breadcrumb_item($sub_tabs['newpoints_myrpg_characters_characters_add']['title'], $sub_tabs['newpoints_myrpg_characters_characters_add']['link']);
			$page->output_header($sub_tabs['newpoints_myrpg_characters_characters_add']['title']);
			$page->output_nav_tabs($sub_tabs, 'newpoints_myrpg_characters_characters_add');
		}
		else
		{
			if(!($character = $myrpg->characters_get($mybb->get_input('chid', 1))))
			{
				$myrpg->admin_redirect(array('view'=>'category','cid'=>$mybb->get_input('cid', 1)), $lang->newpoints_errors_invalid_character, true);
			}

			$mybb->input = array_merge($character, $mybb->input);

			$page->add_breadcrumb_item(htmlspecialchars_uni($character['name']), $myrpg->admin_action_get(array('view'=>'category','cid'=>$mybb->get_input('cid', 1))));
			$page->add_breadcrumb_item($sub_tabs['newpoints_myrpg_characters_characters_edit']['title'], $sub_tabs['newpoints_myrpg_characters_characters_edit']['link']);
			$page->output_header($sub_tabs['newpoints_myrpg_characters_characters_edit']['title']);
			$page->output_nav_tabs($sub_tabs, 'newpoints_myrpg_characters_characters_edit');
		}

		if($mybb->request_method == 'post')
		{
			$myrpg->characters_set(array(
				'name'		=> $mybb->get_input('name'),
				'disporder'	=> $mybb->get_input('disporder', 1),
				'cid'		=> $mybb->get_input('cid', 1)
			));

			if($myrpg->characters_validate())
			{
				if($add)
				{
					$myrpg->characters_insert($myrpg->character);
				}
				else
				{
					$myrpg->characters_update($myrpg->character, $mybb->get_input('chid', 1));
				}

				log_admin_action($mybb->get_input('chid', 1));
				$myrpg->admin_redirect(array('view'=>'category','cid'=>$mybb->get_input('cid', 1)), $lang->newpoints_myrpg_message_success_action);
			}
			else
			{
				$page->output_inline_error($myrpg->errors_get());
			}
		}

		if($add)
		{
			$form = new Form($sub_tabs['newpoints_myrpg_characters_characters_add']['link'], 'post');
			$form_container = new FormContainer($sub_tabs['newpoints_myrpg_characters_characters_add']['description']);
		}
		else
		{
			$form = new Form($sub_tabs['newpoints_myrpg_characters_characters_edit']['link'], 'post');
			$form_container = new FormContainer($sub_tabs['newpoints_myrpg_characters_characters_edit']['description']);
		}

		$form_container->output_row($lang->newpoints_myrpg_name.' <em>*</em>', $lang->newpoints_myrpg_name_desc, $form->generate_text_box('name', $mybb->get_input('name')));
		$form_container->output_row($lang->newpoints_myrpg_disporder.' <em>*</em>', $lang->newpoints_myrpg_disporder_desc, $form->generate_numeric_field('disporder', $mybb->get_input('disporder', 1), array('style' => 'width: 50px;')));

		$form_container->end();

		$form->output_submit_wrapper(array($form->generate_submit_button($lang->newpoints_myrpg_submit), $form->generate_reset_button($lang->reset)));

		$form->end();

		$page->output_footer();
	}
	elseif($mybb->get_input('action') == 'delete')
	{
		if(!($character = $myrpg->characters_get($mybb->get_input('chid', 1))))
		{
			$myrpg->admin_redirect(array('view'=>'category','cid'=>$mybb->get_input('cid', 1)), $lang->newpoints_errors_invalid_character, true);
		}

		if($mybb->request_method == 'post')
		{
			if(isset($mybb->input['no']) || $mybb->input['my_post_key'] != $mybb->post_code)
			{
				$myrpg->admin_redirect(array('view'=>'category','cid'=>$mybb->get_input('cid', 1)));
			}

			$myrpg->characters_delete($character['chid']);

			log_admin_action();

			$myrpg->admin_redirect(array('view'=>'category','cid'=>$mybb->get_input('cid', 1)), $lang->newpoints_myrpg_message_success_action);
		}

		$page->add_breadcrumb_item(htmlspecialchars_uni($character['name']), array('view'=>'category','cid'=>$mybb->get_input('cid', 1)));

		$page->add_breadcrumb_item($lang->delete);

		$page->output_confirm_action($myrpg->admin_action_get(array('view'=>'category','action'=>'delete','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1))));
	}
	else
	{
		$page->output_header($lang->newpoints_myrpg.' - '.$lang->newpoints_myrpg_characters);
		$page->output_nav_tabs($sub_tabs, 'newpoints_myrpg_characters_characters');

		$table = new Table;
		$table->construct_header($lang->newpoints_myrpg_name, array('width' => '65%'));
		$table->construct_header($lang->newpoints_myrpg_disporder, array('width' => '20%', 'class' => 'align_center'));
		$table->construct_header($lang->options, array('width' => '15%', 'class' => 'align_center'));

		$query = $db->simple_select('newpoints_myrpg_characters_characters', '*', "cid='{$category['cid']}'", array('order_by' => 'disporder'));

		if(!$db->num_rows($query))
		{
			$table->construct_cell('<div align="center">'.$lang->newpoints_myrpg_empty.'</div>', array('colspan' => 3));
			$table->construct_row();

			$table->output($sub_tabs['newpoints_myrpg_characters_characters']['description']);
		}
		else
		{
			if($mybb->request_method == 'post')
			{
				foreach($mybb->get_input('disporder', 2) as $chid => $disporder)
				{
					$myrpg->characters_update(array('disporder' => $disporder), $chid);
				}
				$myrpg->admin_redirect(array('view'=>'category','cid'=>$mybb->get_input('cid', 1)));
			}

			$form = new Form($sub_tabs['newpoints_myrpg_characters_characters']['link'], 'post');

			while($character = $db->fetch_array($query))
			{
				$character['cid'] = (int)$character['cid'];
				$character['chid'] = (int)$character['chid'];
				$character['name'] = htmlspecialchars_uni($character['name']);
				$character['disporder'] = (int)$character['disporder'];

				$table->construct_cell('<a href="'.$myrpg->admin_action_get(array('view'=>'levels','cid'=>$character['cid'],'chid'=>$character['chid'])).'">'.htmlspecialchars_uni($character['name']).'</a>');

				$table->construct_cell($form->generate_text_box('disporder['.$character['chid'].']', $character['disporder'], array('style' => 'width: 30px;')), array('class' => 'align_center'));
		
				$popup = new PopupMenu('character_'.$character['chid'], $lang->options);
				$popup->add_item($lang->edit, $myrpg->admin_action_get(array('view'=>'category','action'=>'edit','cid'=>$mybb->get_input('cid', 1),'chid'=>$character['chid'])));
				$popup->add_item($lang->delete, $myrpg->admin_action_get(array('view'=>'category','action'=>'delete','cid'=>$mybb->get_input('cid', 1),'chid'=>$character['chid'])));
		
				$table->construct_cell($popup->fetch(), array('class' => 'align_center'));

				$table->construct_row();
			}

			$table->output($sub_tabs['newpoints_myrpg_characters_characters']['description']);

			$form->output_submit_wrapper(array($form->generate_submit_button($lang->newpoints_myrpg_update_disporder), $form->generate_reset_button($lang->reset)));
			$form->end();
		}

		$page->output_footer();
	}
}
elseif($mybb->get_input('view') == 'levels')
{
	if(!($category = $myrpg->characters_category_get($mybb->get_input('cid', 1))))
	{
		$myrpg->admin_redirect(null, $lang->newpoints_errors_invalid_category, true);
	}

	if(!($character = $myrpg->characters_get($mybb->get_input('chid', 1))))
	{
		$myrpg->admin_redirect(array('view'=>'category','cid'=>$mybb->get_input('cid', 1)), $lang->newpoints_errors_invalid_character, true);
	}

	$category['cid'] = (int)$category['cid'];

	// Page tabs
	$sub_tabs['newpoints_myrpg_characters_levels'] = array(
		'title'			=> $lang->newpoints_myrpg_levels,
		'link'			=> $myrpg->admin_action_get(array('view'=>'levels','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1))),
		'description'	=> $lang->newpoints_myrpg_characters_levels_desc
	);
	$sub_tabs['newpoints_myrpg_characters_levels_add'] = array(
		'title'			=> $lang->newpoints_myrpg_add,
		'link'			=> $myrpg->admin_action_get(array('view'=>'levels','action'=>'add','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1))),
		'description'	=> $lang->newpoints_myrpg_characters_levels_add_desc
	);
	if($mybb->get_input('action') == 'edit')
	{
		$sub_tabs['newpoints_myrpg_characters_levels_edit'] = array(
			'title'			=> $lang->newpoints_myrpg_edit,
			'link'			=> $myrpg->admin_action_get(array('view'=>'levels','action'=>'edit','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1),'lid'=>$mybb->get_input('lid', 1))),
			'description'	=> $lang->newpoints_myrpg_characters_levels_edit_desc
		);
	}

	$page->add_breadcrumb_item($lang->newpoints_myrpg_characters, $myrpg->admin_action_get());
	$page->add_breadcrumb_item(htmlspecialchars_uni($category['name']), $myrpg->admin_action_get(array('view'=>'category','cid'=>$mybb->get_input('cid', 1))));
	$page->add_breadcrumb_item(htmlspecialchars_uni($character['name']), $myrpg->admin_action_get(array('view'=>'levels','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1),'lid'=>$mybb->get_input('lid', 1))));
	$page->add_breadcrumb_item($sub_tabs['newpoints_myrpg_characters_levels']['title'], $sub_tabs['newpoints_myrpg_characters_levels']['link']);

	if($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit')
	{
		$add = ($mybb->get_input('action') == 'add' ? true : false);

		if($add)
		{
			$page->add_breadcrumb_item($sub_tabs['newpoints_myrpg_characters_levels_add']['title'], $sub_tabs['newpoints_myrpg_characters_levels_add']['link']);
			$page->output_header($sub_tabs['newpoints_myrpg_characters_levels_add']['title']);
			$page->output_nav_tabs($sub_tabs, 'newpoints_myrpg_characters_levels_add');
		}
		else
		{
			if(!($level = $myrpg->characters_level_get($mybb->get_input('lid', 1))))
			{
				$myrpg->admin_redirect(array('view'=>'levels','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1),'lid'=>$mybb->get_input('lid', 1)), $lang->newpoints_errors_invalid_level, true);
			}

			$mybb->input = array_merge($level, $mybb->input);

			$page->add_breadcrumb_item($sub_tabs['newpoints_myrpg_characters_levels_edit']['title'], $sub_tabs['newpoints_myrpg_characters_levels_edit']['link']);
			$page->output_header($sub_tabs['newpoints_myrpg_characters_levels_edit']['title']);
			$page->output_nav_tabs($sub_tabs, 'newpoints_myrpg_characters_levels_edit');
		}

		if($mybb->request_method == 'post')
		{
			$myrpg->characters_level_set(array(
				'level'		=> $mybb->get_input('level'),
				'price'		=> $mybb->get_input('price', 3),
				'chid'		=> $mybb->get_input('chid', 1)
			));

			if($myrpg->characters_level_validate())
			{
				if($add)
				{
					$myrpg->characters_level_insert($myrpg->level);
				}
				else
				{
					$myrpg->characters_level_update($myrpg->level, $mybb->get_input('lid', 1));
				}

				log_admin_action($mybb->get_input('lid', 1));
				$myrpg->admin_redirect(array('view'=>'levels','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1)), $lang->newpoints_myrpg_message_success_action);
			}
			else
			{
				$page->output_inline_error($myrpg->errors_get());
			}
		}

		if($add)
		{
			$form = new Form($sub_tabs['newpoints_myrpg_characters_levels_add']['link'], 'post');
			$form_container = new FormContainer($sub_tabs['newpoints_myrpg_characters_levels_add']['description']);
		}
		else
		{
			$form = new Form($sub_tabs['newpoints_myrpg_characters_levels_edit']['link'], 'post');
			$form_container = new FormContainer($sub_tabs['newpoints_myrpg_characters_levels_edit']['description']);
		}

		$form_container->output_row($lang->newpoints_myrpg_level.' <em>*</em>', $lang->newpoints_myrpg_level_desc, $form->generate_numeric_field('level', (int)$mybb->get_input('level', 1)));
		$form_container->output_row($lang->newpoints_myrpg_price.' <em>*</em>', $lang->newpoints_myrpg_price_desc, $form->generate_text_box('price', $mybb->get_input('price', 3)));

		$form_container->end();

		$form->output_submit_wrapper(array($form->generate_submit_button($lang->newpoints_myrpg_submit), $form->generate_reset_button($lang->reset)));

		$form->end();

		$page->output_footer();
	}
	elseif($mybb->get_input('action') == 'delete')
	{
		if(!($level = $myrpg->characters_level_get($mybb->get_input('lid', 1))))
		{
			$myrpg->admin_redirect(array('view'=>'levels','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1),'lid'=>$mybb->get_input('lid', 1)), $lang->newpoints_errors_invalid_level, true);
		}

		if($mybb->request_method == 'post')
		{
			if(isset($mybb->input['no']) || $mybb->input['my_post_key'] != $mybb->post_code)
			{
				$myrpg->admin_redirect(array('view'=>'levels','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1),'lid'=>$mybb->get_input('lid', 1)));
			}

			$myrpg->characters_level_delete($level['lid']);
			log_admin_action();
			$myrpg->admin_redirect(array('view'=>'levels','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1)), $lang->newpoints_myrpg_message_success_action);
		}

		$page->add_breadcrumb_item($lang->delete);

		$page->output_confirm_action($myrpg->admin_action_get(array('view'=>'levels','action'=>'delete','cid'=>$mybb->get_input('cid', 1),'chid'=>$mybb->get_input('chid', 1),'lid'=>$mybb->get_input('lid', 1))));
	}
	else
	{
		$page->output_header($lang->newpoints_myrpg.' - '.$lang->newpoints_myrpg_characters);
		$page->output_nav_tabs($sub_tabs, 'newpoints_myrpg_characters_levels');

		$table = new Table;
		$table->construct_header($lang->newpoints_myrpg_level, array('width' => '20%', 'class' => 'align_center'));
		$table->construct_header($lang->newpoints_myrpg_price, array('width' => '25%', 'class' => 'align_center'));
		$table->construct_header($lang->options, array('width' => '15%', 'class' => 'align_center'));

		$query = $db->simple_select('newpoints_myrpg_characters_levels', '*', "chid='{$mybb->get_input('chid', 1)}'", array('order_by' => 'level'));

		if(!$db->num_rows($query))
		{
			$table->construct_cell('<div align="center">'.$lang->newpoints_myrpg_empty.'</div>', array('colspan' => 3));
			$table->construct_row();

			$table->output($sub_tabs['newpoints_myrpg_characters_levels']['description']);
		}
		else
		{
			while($level = $db->fetch_array($query))
			{
				$level['lid'] = (int)$level['lid'];
				$level['chid'] = (int)$level['chid'];
				$level['level'] = (int)$level['level'];
				$level['price'] = (float)$level['price'];

				$table->construct_cell(my+number_format($level['level']), array('class' => 'align_center'));
				$table->construct_cell(newpoints_format_points($level['price']), array('class' => 'align_center'));

				$popup = new PopupMenu('level_'.$level['lid'], $lang->options);
				$popup->add_item($lang->edit, $myrpg->admin_action_get(array('view'=>'levels','action'=>'edit','cid'=>$mybb->get_input('cid', 1),'chid'=>$level['chid'],'lid'=>$level['lid'])));
				$popup->add_item($lang->delete, $myrpg->admin_action_get(array('view'=>'levels','action'=>'delete','cid'=>$mybb->get_input('cid', 1),'chid'=>$level['chid'],'lid'=>$level['lid'])));
				$table->construct_cell($popup->fetch(), array('class' => 'align_center'));

				$table->construct_row();
			}

			$table->output($sub_tabs['newpoints_myrpg_characters_levels']['description']);
		}

		$page->output_footer();
	}
}
else
{
	$page->add_breadcrumb_item($lang->newpoints_myrpg_characters, $myrpg->admin_action_get());

	// Page tabs
	$sub_tabs['newpoints_myrpg_characters_categories_add'] = array(
		'title'			=> $lang->newpoints_myrpg_add,
		'link'			=> $myrpg->admin_action_get('action=add'),
		'description'	=> $lang->newpoints_myrpg_characters_categories_add_desc
	);

	if($mybb->get_input('action') == 'edit')
	{
		$sub_tabs['newpoints_myrpg_characters_categories_edit'] = array(
			'title'			=> $lang->newpoints_myrpg_edit,
			'link'			=> $myrpg->admin_action_get(array('action'=>'edit','cid'=>$mybb->get_input('cid', 1))),
			'description'	=> $lang->newpoints_myrpg_characters_categories_edit_desc
		);
	}

	if($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit')
	{
		$add = ($mybb->get_input('action') == 'add' ? true : false);

		if($add)
		{
			$page->add_breadcrumb_item($sub_tabs['newpoints_myrpg_characters_categories_add']['title'], $sub_tabs['newpoints_myrpg_characters_categories_add']['link']);
			$page->output_header($sub_tabs['newpoints_myrpg_characters_categories_add']['title']);
			$page->output_nav_tabs($sub_tabs, 'newpoints_myrpg_characters_categories_add');
		}
		else
		{
			if(!($category = $myrpg->characters_category_get($mybb->get_input('cid', 1))))
			{
				$myrpg->admin_redirect(null, $lang->newpoints_errors_invalid_category, true);
			}

			$mybb->input = array_merge($category, $mybb->input);

			$page->add_breadcrumb_item($sub_tabs['newpoints_myrpg_characters_categories_edit']['title'], $sub_tabs['newpoints_myrpg_characters_categories_edit']['link']);
			$page->output_header($sub_tabs['newpoints_myrpg_characters_categories_edit']['title']);
			$page->output_nav_tabs($sub_tabs, 'newpoints_myrpg_characters_categories_edit');
		}

		if($mybb->request_method == 'post')
		{
			$myrpg->characters_category_set(array(
				'name'		=> $mybb->get_input('name'),
				'disporder'	=> $mybb->get_input('disporder', 1)
			));

			if($myrpg->characters_category_validate())
			{
				if($add)
				{
					$myrpg->characters_category_insert($myrpg->category);
				}
				else
				{
					$myrpg->characters_category_update($myrpg->category, $mybb->get_input('cid', 1));
				}

				log_admin_action($mybb->get_input('cid', 1));
				$myrpg->admin_redirect(null, $lang->newpoints_myrpg_message_success_action);
			}
			else
			{
				$page->output_inline_error($myrpg->errors_get());
			}
		}

		if($add)
		{
			$form = new Form($sub_tabs['newpoints_myrpg_characters_categories_add']['link'], 'post');
			$form_container = new FormContainer($sub_tabs['newpoints_myrpg_characters_categories_add']['description']);
		}
		else
		{
			$form = new Form($sub_tabs['newpoints_myrpg_characters_categories_edit']['link'], 'post');
			$form_container = new FormContainer($sub_tabs['newpoints_myrpg_characters_categories_edit']['description']);
		}

		$form_container->output_row($lang->newpoints_myrpg_name.' <em>*</em>', $lang->newpoints_myrpg_name_desc, $form->generate_text_box('name', $mybb->get_input('name')));
		$form_container->output_row($lang->newpoints_myrpg_disporder.' <em>*</em>', $lang->newpoints_myrpg_disporder_desc, $form->generate_numeric_field('disporder', $mybb->get_input('disporder', 1), array('style' => 'width: 50px;')));

		$form_container->end();

		$form->output_submit_wrapper(array($form->generate_submit_button($lang->newpoints_myrpg_submit), $form->generate_reset_button($lang->reset)));

		$form->end();

		$page->output_footer();
	}
	elseif($mybb->get_input('action') == 'delete')
	{
		if(!($category = $myrpg->characters_category_get($mybb->get_input('cid', 1))))
		{
			$myrpg->admin_redirect(null, $lang->newpoints_errors_invalid_category, true);
		}

		if($mybb->request_method == 'post')
		{
			if(isset($mybb->input['no']) || $mybb->get_input('my_post_key') != $mybb->post_code)
			{
				$myrpg->admin_redirect();
			}

			$myrpg->characters_category_delete($category['cid']);

			log_admin_action();

			$myrpg->admin_redirect(null, $lang->newpoints_myrpg_message_success_action);
		}

		$page->add_breadcrumb_item(htmlspecialchars_uni($category['name']), $myrpg->admin_action_get());

		$page->add_breadcrumb_item($lang->delete);

		$page->output_confirm_action($myrpg->admin_action_get(array('action'=>'delete','cid'=>(int)$category['cid'])));
	}
	else
	{
		$page->output_header($lang->newpoints_myrpg.' - '.$lang->newpoints_myrpg_characters);
		$page->output_nav_tabs($sub_tabs, 'newpoints_myrpg_characters_categories');

		$table = new Table;
		$table->construct_header($lang->newpoints_myrpg_name, array('width' => '60%'));
		$table->construct_header($lang->newpoints_myrpg_disporder, array('width' => '20%', 'class' => 'align_center'));
		$table->construct_header($lang->options, array('width' => '15%', 'class' => 'align_center'));

		$query = $db->simple_select('newpoints_myrpg_characters_categories', '*', '', array('order_by' => 'disporder'));

		if(!$db->num_rows($query))
		{
			$table->construct_cell('<div align="center">'.$lang->newpoints_myrpg_empty.'</div>', array('colspan' => 4));
			$table->construct_row();

			$table->output($sub_tabs['newpoints_myrpg_characters_categories']['description']);
		}
		else
		{
			if($mybb->request_method == 'post')
			{
				foreach($mybb->get_input('disporder', 2) as $cid => $disporder)
				{
					$myrpg->characters_category_update(array('disporder' => $disporder), $cid);
				}
				$myrpg->admin_redirect();
			}

			$form = new Form($sub_tabs['newpoints_myrpg_characters_categories']['link'], 'post');

			while($category = $db->fetch_array($query))
			{
				$category['cid'] = (int)$category['cid'];
				$category['name'] = htmlspecialchars_uni($category['name']);
				$category['disporder'] = (int)$category['disporder'];

				$table->construct_cell('<a href="'.$myrpg->admin_action_get(array('view'=>'category','cid'=>$category['cid'])).'">'.htmlspecialchars_uni($category['name']).'</a>');
				$table->construct_cell($form->generate_numeric_field('disporder', $category['disporder'], array('style' => 'width: 50px;')), array('class' => 'align_center'));
		
				$popup = new PopupMenu('category_'.$category['cid'], $lang->options);
				$popup->add_item($lang->edit, $myrpg->admin_action_get(array('action'=>'edit','cid'=>$category['cid'])));
				$popup->add_item($lang->delete, $myrpg->admin_action_get(array('action'=>'delete','cid'=>$category['cid'])));
		
				$table->construct_cell($popup->fetch(), array('class' => 'align_center'));

				$table->construct_row();
			}

			$table->output($sub_tabs['newpoints_myrpg_characters_categories']['description']);

			$form->output_submit_wrapper(array($form->generate_submit_button($lang->newpoints_myrpg_update_disporder), $form->generate_reset_button($lang->reset)));
			$form->end();
		}

		$page->output_footer();
	}
}