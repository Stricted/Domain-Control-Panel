{include file="header.tpl"}
<div class="row">
	<div class="col-lg-12">
		<ol class="breadcrumb">
			<li class="active"><a href="{link controller='DomainList'}{/link}"><i class="fa fa-home"></i> Domain Control Panel</a></li>
		</ol>
	</div>
</div>
{if $isReseller === true}
<div class="row">
	<div class="col-lg-12">
		<div class="page-header pull-right">
			<a href="{link controller='DomainAdd'}{/link}" class="btn btn-gr-gray"><i class="fa fa-plus"></i> Domain hinzufügen</a>
		</div>
	</div>
</div>
{/if}
{hascontent}
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">Domains <span class="badge badge-black">{$count}</span></div>
				<div class="panel-body">
					{pages controller='DomainList'}
					<div class="table-responsive">
						<table class="table table-bordered table-hover radius table-striped">
							<thead>
								<tr>
									<th><a class="sorting{if $sortField == 'id'}_{$sortOrder|strtolower}{/if}" href="{link controller='DomainList'}&pageNo={$pageNo}&sortField=id&sortOrder={if $sortField == 'id' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">ID</a></th>
									<th><a class="sorting{if $sortField == 'origin'}_{$sortOrder|strtolower}{/if}" href="{link controller='DomainList'}&pageNo={$pageNo}&sortField=origin&sortOrder={if $sortField == 'origin' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">Name</a></th>
									<th><a class="sorting{if $sortField == 'serial'}_{$sortOrder|strtolower}{/if}" href="{link controller='DomainList'}&pageNo={$pageNo}&sortField=serial&sortOrder={if $sortField == 'serial' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">Serial</a></th>
									<th>Records</th>
									{if $isReseller === true || $smarty.const.ENABLE_DNSSEC}<th>Manage</th>{/if}
								</tr>
							</thead>
							
							<tbody>
								{content}
								{foreach from=$domains item=domain}
								<tr>
									<td>{$domain['id']}</td>
									<td>{if $domain['active'] != 1}<span class="badge badge-red">{lang}domain.disabled{/lang}</span> {/if}<a href="{link controller='RecordList' id=$domain['id'] title=$domain['origin']}{/link}">{$domain['origin']}</a></td>
									<td>{$domain['serial']}</td>
									<td>{$domain['rrc']}</td>
									<td>
										{if $isReseller === true}<span class="fa fa{if $domain['active']}-check{/if}-square-o ttips pointer toggleDomain" toggle-id="{$domain['id']}" title="{if $domain['active']}{lang}button.disable{/lang}{else}{lang}button.enable{/lang}{/if}" data-disable-message="{lang}button.disable{/lang}" data-enable-message="{lang}button.enable{/lang}"></span>&nbsp;{/if}
										{if $smarty.const.ENABLE_DNSSEC}<a href="{link controller='SecList' id=$domain['id'] title=$domain['origin']}{/link}" class="ttips" title="Edit DNSSEC"><span class="fa fa-key"></span></a>&nbsp;{/if}
										{if $isReseller === true}<span class="fa fa-remove ttips pointer deleteDomain" delete-id="{$domain['id']}" delete-confirm="{lang}domain.delete.message{/lang}" title="{lang}button.delete{/lang}"></span>{/if}
									</td>
								</tr>
								{/foreach}
								{/content}
							</tbody>
						</table>
					</div>
					{pages controller='DomainList'}
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