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
		<link href="css/default/sb-admin-2{if !$smarty.const.ENABLE_DEBUG}.min{/if}.css" rel="stylesheet">
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-md-4 col-md-offset-4">
					<div class="login-panel panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Please Sign In</h3>
						</div>
						<div class="panel-body">
							<form method="post" action="{link controller='Login'}{/link}">
								<fieldset>
									<div class="form-group">
										<input class="form-control" placeholder="Username" name="username" type="username" autofocus>
									</div>
									<div class="form-group">
										<input class="form-control" placeholder="Password" name="password" type="password" value="">
									</div>
									<div class="checkbox">
											<label>
										<input name="remember" type="checkbox" value="Remember Me">Remember Me
										</label>
									</div>
									<input name="submit" type="submit" class="btn btn-lg btn-success btn-block" value="Login" />
								</fieldset>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="js/default/jquery{if !$smarty.const.ENABLE_DEBUG}.min{/if}.js"></script>
		<script src="js/default/bootstrap{if !$smarty.const.ENABLE_DEBUG}.min{/if}.js"></script>
	</body>
</html>
