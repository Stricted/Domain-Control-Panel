{include file="header.tpl"}
<div class="row">
	<div class="col-lg-12">
		<ol class="breadcrumb">
			<li><a href="{link controller='DomainList'}{/link}"><i class="fa fa-home"></i> Domain Control Panel</a></li>
			<li class="active"><a href="{link controller='ApiManagement'}{/link}">API</a></li>
		</ol>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">API</div>
			<div class="panel-body">
				<div class="dataTable_wrapper">
					<fieldset>
						<dl>
							<dt>userID</dt>
							<dd>{$userID}</dd>
						</dl>
						<dl>
							<dt>API-Key</dt>
							<dd id="apiKey">
							{if !empty($apiKey)}
								{$apiKey}
							{else}
								<button id="requestApiKey" type="submit">request API-Key</button>
							{/if}
							</dd>
						</dl>
					</fieldset>
				</div>
			</div>
		</div>
	</div>
</div>
{include file="footer.tpl"}