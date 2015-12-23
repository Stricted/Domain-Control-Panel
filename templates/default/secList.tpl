{include file="header.tpl"}
<div class="c-block" id="breadcrumbs">
	<ol class="breadcrumb">
		<li><a href="{link controller='DomainList'}{/link}"><i class="fa fa-home"></i> Domain Control Panel</a></li>
		<li class="active"><a href="{link controller='SecList' id=$soa['id'] title=$soa['origin']}{/link}">{$soa['origin']}</a></li>
	</ol>
</div>
{if !empty($ds)}
	<div class="alert alert-icon alert-info">
		{$soa['origin']} IN DS {$ds['keyid']} {$ds['algo']} 1 {$ds['sha1']}<br />
		{$soa['origin']} IN DS {$ds['keyid']} {$ds['algo']} 2 {$ds['sha256']}
	</div>
{/if}
{hascontent}
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">Records <span class="badge badge-black">{$records|count}</span></div>
				<div class="panel-body">
					<div class="table-responsive">
						<table class="table table-bordered table-hover radius table-striped">
							<thead>
								<tr>
									<th>Algorithmus</a></th>
									<th>Type</th>
									<th>DNSSEC Public Key</a></th>
									<th>DNSSEC Private Key</th>
									<th>verwalten</th>
								</tr>
							</thead>

							<tbody>
								{content}
									{foreach from=$records item=record}
									<tr>
										<td>{$record['algo']}</td>
										<td>{if $record['active'] != 1}<span class="badge badge-red">{lang}domain.disabled{/lang}</span> {/if}{$record['type']}</td>
										<td>{$record['public']|substr:0:20}&hellip;</td>
										<td>{$record['private']|substr:0:20}&hellip;</td>
										<td>
											<span class="fa fa-pencil ttips pointer" title="Edit"></span>&nbsp;
											<span class="fa fa{if $record['active']}-check{/if}-square-o ttips pointer toggleSec" toggle-id="{$record['id']}" title="{if $record['active']}{lang}button.disable{/lang}{else}{lang}button.enable{/lang}{/if}" data-disable-message="{lang}button.disable{/lang}" data-enable-message="{lang}button.enable{/lang}"></span>&nbsp;
											<span class="fa fa-remove ttips pointer deleteSec" delete-id="{$record['id']}" delete-confirm="{lang}record.delete.message{/lang}" title="{lang}button.delete{/lang}"></span>
										</td>
									</tr>
									{/foreach}
								{/content}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
{hascontentelse}
	<div class="alert alert-icon alert-info">
		<i class="fa fa-info-circle"></i> No Records found.
	</div>
{/hascontent}
{include file="footer.tpl"}