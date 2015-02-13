<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		<title>Domain Control Panel</title>
		<link href="css/default/bootstrap{if !$smarty.const.ENABLE_DEBUG_MODE}.min{/if}.css" rel="stylesheet">
		<link href="css/default/sb-admin-2.css" rel="stylesheet">
		<link href="css/default/font-awesome{if !$smarty.const.ENABLE_DEBUG_MODE}.min{/if}.css" rel="stylesheet" type="text/css">
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
		<style>
			{literal}
			body {
				padding-top: 20px;
				color: #fff;
			}
			.body-content {
				padding-left: 15px;
				padding-right: 15px;
			}
			
			.jumbotron {
				text-align: center;
				background-color: transparent;
				font-size: 21px;
				font-weight: 200;
				line-height: 2.1428571435;
				color: inherit;
				padding: 10px 60px;
			}
			.jumbotron .btn {
				font-size: 21px;
				padding: 14px 24px;
			}

			.green {
				color:#5cb85c;
			}
			{/literal}
		</style>
	</head>
	<body onload="javascript:loadDomain();">
		<div class="container">
			<div class="jumbotron">
				<h1><i class="fa fa-cogs green"></i> Temporary Maintenance</h1>
				<p class="lead">The web server for <em><span id="display-domain"></span></em> is currently undergoing some maintenance.</p>
				<a href="javascript:document.location.reload(true);" class="btn btn-default btn-lg text-center"><span class="green">Try This Page Again</span></a>
			</div>
		</div>
		<div class="container">
			<div class="body-content">
				<div class="row">
					<div class="col-md-6">
						<h2>What happened?</h2>
						<p class="lead">Servers and websites need regular maintnance just like a car to keep them up and running smoothly.</p>
					</div>
					<div class="col-md-6">
						<h2>What can I do?</h2>
						<p class="lead">If you're a site vistor</p>
						<p>If you need immediate assistance, please send us an email instead. We apologize for any inconvenience.</p>
						<p class="lead">If you're the site owner</p>
						<p>The maintenance period will mostly likely be very brief, the best thing to do is to check back in a few minutes and everything will probably be working normal agian.</p>
					</div>
				</div>
			</div>
		</div>

		<script src="js/default/jquery{if !$smarty.const.ENABLE_DEBUG_MODE}.min{/if}.js"></script>
		<script src="js/default/bootstrap{if !$smarty.const.ENABLE_DEBUG_MODE}.min{/if}.js"></script>
		<script type="text/javascript">
			{literal}
			function loadDomain() {
				var display = document.getElementById("display-domain");
				display.innerHTML = document.domain;
			}
			{/literal}
		</script>
	</body>
</html>
