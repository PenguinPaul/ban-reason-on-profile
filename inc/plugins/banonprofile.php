<?php

if(!defined("IN_MYBB"))
{
	die('Nope.');
}

$plugins->add_hook("member_profile_end", "banonprofile");

function banonprofile_info()
{
	return array(
		"name"			=> "Ban Info on Profile",
		"description"	=> "Shows the ban information of a banned user on their profile.",
		"website"		=> "http://mybbpl.us",
		"author"		=> "Paul Hedman",
		"authorsite"	=> "http://www.paulhedman.com",
		"version"		=> "1.1",
		"guid" 			=> "",
		"compatibility" => "*"
	);
}

function banonprofile_activate()
{
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

	find_replace_templatesets("member_profile", "#".preg_quote('{$header}')."#i", '{$header}{$banreason}');

}

function banonprofile_deactivate()
{
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

	find_replace_templatesets("member_profile", "#".preg_quote('{$banreason}')."#i", '');

}

function banonprofile()
{
	global $parser;
	global $mybb,$db,$memprofile,$banreason;
	
	$usergroup = $db->fetch_array($db->simple_select("usergroups","*","gid='{$memprofile['usergroup']}'"));
	if($usergroup['isbannedgroup'] == '1')
	{
		$ban = $db->fetch_array($db->simple_select("banned","*","uid='{$memprofile['uid']}'"));
		
		if($ban['lifted'] != '0')
		{
			$bantime = my_date($mybb->settings['timeformat'], $ban['lifted']);
			$bandate = my_date($mybb->settings['dateformat'], $ban['lifted']);
			$lifted = "until {$bandate} at {$bantime}";
		} else {
			$lifted = "permanently";
		}
		
		if($ban['reason'] == "")
		{
			$ban['reason'] = "(None specified)";
		}

		if($ban['reason'])
		{
			if(!($parser instanceof postParser))
			{
				require_once MYBB_ROOT."inc/class_parser.php";

				$parser = new postParser;
			}

			$ban['reason'] = htmlspecialchars_uni($parser->parse_badwords($ban['reason']));
		}
		else
		{
			$ban['reason'] = $lang->na;
		}

		$banreason = "<div class=\"red_alert\" style=\"text-align:center;\">The user {$memprofile['username']} is banned {$lifted} for the following reason: {$ban['reason']}</div>";
	} else {
		$banreason = '';
	}
}
?>
