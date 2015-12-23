{include file="header.tpl"}
<div class="row">
	<div class="col-lg-12">
		<ol class="breadcrumb">
			<li><a href="{link controller='DomainList'}{/link}"><i class="fa fa-home"></i> Domain Control Panel</a></li>
			<li class="active"><a href="{link controller='RecordList' id=$soa['id'] title=$soa['origin']}{/link}">{$soa['origin']}</a></li>
		</ol>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="page-header pull-right">
			<a href="{link controller='RecordList' id=$soa['id'] title=$soa['origin']}{/link}" class="btn btn-gr-gray"><i class="fa fa-list"></i> Einträge auflisten</a>
		</div>
	</div>
</div>
{if isset($success)}
	<div class="alert alert-success">
		Record erfolgreich hinzugefügt.
	</div>
{/if}
<form method="post" action="{link controller='RecordAdd' id=$soa['id']}{/link}">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">Add Record</div>
				<div class="panel-body">
					<div>
						<fieldset>
							<dl>
								<dt>Host</dt>
								<dd {if 'name'|in_array:$error}class="form-group has-error"{/if}>
									<input type="text" id="name" name="name" value="{if isset($name)}{$name|escape}{/if}" maxlength="255" class="form-control medium">
									{if 'name'|in_array:$error}<span class="help-block">Please correct the error</span>{/if}
								</dd>
							</dl>
							<dl>
								<dt>TTL</dt>
								<dd {if 'ttl'|in_array:$error}class="form-group has-error"{/if}>
									<input type="number" id="ttl" name="ttl" value="{if isset($ttl)}{$ttl|escape}{else}60{/if}" maxlength="255" class="form-control medium">
									{if 'ttl'|in_array:$error}<span class="help-block">Please correct the error</span>{/if}
								</dd>
							</dl>
							<dl>
								<dt>Type</dt>
								<dd {if 'type'|in_array:$error}class="form-group has-error"{/if}>
									<select id="type" name="type" class="form-control medium">
										<option label="A" value="A"{if isset($type) && $type == "A"} selected="selected"{/if}>A</option>
										<option label="AAAA" value="AAAA"{if isset($type) && $type == "AAAA"} selected="selected"{/if}>AAAA</option>
										<option label="CNAME" value="CNAME"{if isset($type) && $type == "CNAME"} selected="selected"{/if}>CNAME</option>
										<option label="MX" value="MX"{if isset($type) && $type == "MX"} selected="selected"{/if}>MX</option>
										<option label="PTR" value="PTR"{if isset($type) && $type == "PTR"} selected="selected"{/if}>PTR</option>
										<option label="SRV" value="SRV"{if isset($type) && $type == "SRV"} selected="selected"{/if}>SRV</option>
										<option label="TXT" value="TXT"{if isset($type) && $type == "TXT"} selected="selected"{/if}>TXT</option>
										<!-- <option label="DNSKEY" value="DNSKEY"{if isset($type) && $type == "DNSKEY"} selected="selected"{/if}>DNSKEY</option> -->
										<option label="DS" value="DS"{if isset($type) && $type == "DS"} selected="selected"{/if}>DS</option>
										<option label="TLSA" value="TLSA"{if isset($type) && $type == "TLSA"} selected="selected"{/if}>TLSA</option>
										<option label="NS" value="NS"{if isset($type) && $type == "NS"} selected="selected"{/if}>NS</option>
									</select>
									{if 'type'|in_array:$error}<span class="help-block">Please correct the error</span>{/if}
								</dd>
							</dl>
							<dl id="aux"{if isset($type) && $type == "SRV" && $type == "DS" && $type == "TLSA" &&  $type == "MX"}{else} style="display: none;"{/if}>
								<dt>{if isset($type) && $type == "DS"}Key-ID{elseif isset($type) && $type == "TLSA"}Usage{else}Prio{/if}</dt>
								<dd {if 'aux'|in_array:$error}class="form-group has-error"{/if}>
									<input type="number" id="aux" name="aux" value="{if isset($aux)}{$aux|escape}{else}0{/if}" maxlength="255" class="form-control medium">
									{if 'aux'|in_array:$error}<span class="help-block">Please correct the error</span>{/if}
								</dd>
							</dl>
							<dl id="weight"{if isset($type) && $type == "SRV" && $type == "DS" && $type == "TLSA"}{else} style="display: none;"{/if}>
								<dt>{if isset($type) && $type == "DS"}Algorithm{elseif isset($type) && $type == "TLSA"}Selector{else}weight{/if}</dt>
								<dd {if 'weight'|in_array:$error}class="form-group has-error"{/if}>
									<input type="number" id="weight" name="weight" value="{if isset($weight)}{$weight|escape}{else}0{/if}" maxlength="255" class="form-control medium">
									{if 'weight'|in_array:$error}<span class="help-block">Please correct the error</span>{/if}
								</dd>
							</dl>
							<dl id="port"{if isset($type) && $type == "SRV" && $type == "DS" && $type == "TLSA"}{else} style="display: none;"{/if}>
								<dt>{if isset($type) && $type == "DS"}Digest Type{elseif isset($type) && $type == "TLSA"}Hash Type{else}port{/if}</dt>
								<dd {if 'port'|in_array:$error}class="form-group has-error"{/if}>
									<input type="number" id="port" name="port" value="{if isset($port)}{$port|escape}{else}0{/if}" maxlength="255" class="form-control medium">
									{if 'port'|in_array:$error}<span class="help-block">Please correct the error</span>{/if}
								</dd>
							</dl>
							<dl id="data">
								<dt>{if isset($type) && $type == "DS"}Digest{elseif isset($type) && $type == "TLSA"}Hash{else}Data{/if}</dt>
								<dd {if 'data'|in_array:$error}class="form-group has-error"{/if}>
									<input type="text" id="data" name="data" value="{if isset($data)}{$data|escape}{/if}" maxlength="255" class="form-control medium">
									{if 'data'|in_array:$error}<span class="help-block">Please correct the error</span>{/if}
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