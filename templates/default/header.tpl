<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		<title>Domain Control Panel</title>
		<link href="css/default/bootstrap{if !$smarty.const.ENABLE_DEBUG}.min{/if}.css" rel="stylesheet">
		<link href="css/default/metisMenu{if !$smarty.const.ENABLE_DEBUG}.min{/if}.css" rel="stylesheet">
		<link href="css/default/sb-admin-2{if !$smarty.const.ENABLE_DEBUG}.min{/if}.css" rel="stylesheet">
		<link href="css/default/font-awesome{if !$smarty.const.ENABLE_DEBUG}.min{/if}.css" rel="stylesheet" type="text/css">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<div id="wrapper">
			<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="index.html"><i class="fa fa-wrench"></i> Domain Control Panel</a>
				</div>
				
				<ul class="nav navbar-top-links navbar-right">
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
						<i class="fa fa-user fa-fw"></i> {$username} <i class="fa fa-caret-down"></i>
						</a>
						<ul class="dropdown-menu dropdown-user">
							<li><a href="index.php?page=logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>
						</ul>
					</li>
				</ul>
				
				<div class="navbar-default sidebar" role="navigation">
					<div class="sidebar-nav navbar-collapse">
						<ul class="nav" id="side-menu">
							<li{if $activeMenuItem == 'index' || $activeMenuItem == 'add' || $activeMenuItem == 'update'} class="active"{/if}>
								<a href="#"><i class="fa fa-home"></i> Domains<span class="fa arrow"></span></a>
								<ul class="{if $activeMenuItem == 'index' || $activeMenuItem == 'add' || $activeMenuItem == 'update'}nav nav-second-level collapse in{else}nav nav-second-level{/if}">
									<li><a {if $activeMenuItem == 'index'}class="active" {/if}href="index.php?page=DomainList"><i class="fa fa-list"></i> Auflisten</a></li>
									{if $isReseller === true}<li><a {if $activeMenuItem == 'add'}class="active" {/if}href="index.php?page=DomainAdd"><i class="fa fa-plus"></i> Hinzufügen</a></li>{/if}
								</ul>
							</li>
							<li{if $activeMenuItem == 'settings' || $activeMenuItem == 'api'} class="active"{/if}>
								<a href="#"><i class="fa fa-cogs"></i> Settings<span class="fa arrow"></span></a>
								<ul class="{if $activeMenuItem == 'settings' || $activeMenuItem == 'api'}nav nav-second-level collapse in{else}nav nav-second-level{/if}">
									<li><a {if $activeMenuItem == 'index'}class="api" {/if}href="index.php?page=ApiManagement"><i class="fa fa-key"></i> API</a></li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</nav>

			<div id="page-wrapper">