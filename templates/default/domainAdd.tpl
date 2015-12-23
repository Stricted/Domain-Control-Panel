{include file="header.tpl"}
<div class="row">
	<div class="col-lg-12">
		<ol class="breadcrumb">
			<li><a href="{link controller='DomainList'}{/link}"><i class="fa fa-home"></i> Domain Control Panel</a></li>
			<li class="active"><a href="{link controller='DomainAdd'}{/link}">Domain hinzufügen</a></li>
		</ol>
	</div>
</div>
{if isset($success)}
	<div class="alert alert-success">
		Domain erfolgreich hinzugefügt.
	</div>
{/if}
<form method="post" action="{link controller='DomainAdd'}{/link}">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">Domain hinzufügen</div>
				<div class="panel-body">
					<div class="dataTable_wrapper">
						<fieldset>
							<dl>
								<dt><label for="origin">Name</label></dt>
								<dd{if $error=='origin'} class="form-group has-error"{/if}>
									<input type="text" id="origin" name="origin" value="{if isset($origin)}{$origin}{/if}" maxlength="255" class="form-control medium">
									{if $error=='origin'}<span class="help-block">Please correct the error</span>{/if}
								</dd>
							</dl>
						</fieldset>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="formSubmit" style="text-align: center;">
		<input class="btn btn-gr-gray" name="submit" type="submit" value="Submit" >
	</div>
</form>
{include file="footer.tpl"}