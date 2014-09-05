{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 | Erik Hommel (CiviCooP) erik.hommel@civicoop.org                    |
 | PUM issue 152 - 7 Jul 29014                                        |
 | Customized for Country contact sub type                            |
 +--------------------------------------------------------------------+
*}
{* Contact Summary template for new tabbed interface. Replaces Basic.tpl *}

{include file="CRM/common/wysiwyg.tpl" includeWysiwygEditor=true}

{* include overlay js *}
{include file="CRM/common/overlay.tpl"}
<div class="crm-summary-contactname-block crm-inline-edit-container">
  <div class="crm-summary-block" id="contactname-block">
    {include file="CRM/Contact/Page/Inline/ContactName.tpl"}
  </div>
</div>

{if !$summaryPrint}
  <div class="crm-actions-ribbon">
    <ul id="actions">
      {assign var='urlParams' value="reset=1"}
      {if $searchKey}
        {assign var='urlParams' value=$urlParams|cat:"&key=$searchKey"}
      {/if}
      {if $context}
        {assign var='urlParams' value=$urlParams|cat:"&context=$context"}
      {/if}

      {* Previous and Next contact navigation when accessing contact summary from search results. *}
      {if $nextPrevError}
        <li class="crm-next-action">
          {help id="id-next-prev-buttons"}&nbsp;
        </li>
      {else}
        {if $nextContactID}
          {assign var='viewParams' value=$urlParams|cat:"&cid=$nextContactID"}
          <li class="crm-next-action">
            <a href="{crmURL p='civicrm/contact/view' q=$viewParams}" class="view button" title="{$nextContactName}">
              <span title="{$nextContactName}"><div class="icon next-icon"></div>{ts}Next{/ts}</span>
            </a>
          </li>
        {/if}
        {if $prevContactID}
          {assign var='viewParams' value=$urlParams|cat:"&cid=$prevContactID"}
          <li class="crm-previous-action">
            <a href="{crmURL p='civicrm/contact/view' q=$viewParams}" class="view button" title="{$prevContactName}">
              <span title="{$prevContactName}"><div class="icon previous-icon"></div>{ts}Previous{/ts}</span>
            </a>
          </li>
        {/if}
      {/if}

    </ul>
    <div class="clear"></div>
  </div><!-- .crm-actions-ribbon -->
{/if}

<div class="crm-block crm-content-block crm-contact-page crm-inline-edit-container">
  <div id="mainTabContainer" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
    <ul class="crm-contact-tabs-list">
      <li id="tab_summary" class="crm-tab-button">
        <a href="#contact-summary" title="{ts}Summary{/ts}">
          <span> </span> {ts}Summary{/ts}
          <em>&nbsp;</em>
        </a>
      </li>
      {foreach from=$allTabs key=tabName item=tabValue}
        <li id="tab_{$tabValue.id}" class="crm-tab-button crm-count-{$tabValue.count}">
          <a href="{$tabValue.url}" title="{$tabValue.title}">
            <span> </span> {$tabValue.title}
            <em>{$tabValue.count}</em>
          </a>
        </li>
      {/foreach}
    </ul>

    <div id="contact-summary" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
      {if (isset($hookContentPlacement) and ($hookContentPlacement neq 3)) or empty($hookContentPlacement)}

        <div class="contactTopBar contact_panel">
          <div class="contactCardLeft">
            {crmRegion name="contact-basic-info-left"}
            <div class="{if !empty($imageURL)} float-left{/if}">
              <div class="crm-clear crm-summary-block">
                <div class="crm-summary-row">
                  <div class="crm-label">{ts}Contact Type{/ts}</div>
                  <div class="crm-content crm-contact_type_label">
                    {if isset($contact_type_label)}{$contact_type_label}{/if}
                  </div>
                </div>
                <div class="crm-summary-row">
                  <div class="crm-label">
                    {ts}CiviCRM ID{/ts}{if !empty($userRecordUrl)} / {ts}User ID{/ts}{/if}
                  </div>
                  <div class="crm-content">
                    <span class="crm-contact-contact_id">{$contactId}</span>
                    {if !empty($userRecordUrl)}
                      <span class="crm-contact-user_record_id">
                        &nbsp;/&nbsp;<a title="View user record" class="user-record-link" href="{$userRecordUrl}">{$userRecordId}</a>
                      </span>
                    {/if}
                  </div>
                </div>
                  <div class="crm-summary-row">
                    <div class="crm-label">{ts}Country ID{/ts}</div>
                    {foreach from=$viewCustomData key=customGroupId item=customRow}
                      {foreach from=$customRow key=customRecordId item=customData}
                        {foreach from=$customData key=customId item=countryFields}
                          {if $customId eq 'fields'}
                            {foreach from=$countryFields item=countryData}
                              <div class="crm-content">{$countryData.field_value}</div>
                            {/foreach}
                          {/if}
                        {/foreach}  
                      {/foreach}
                    {/foreach}
                  </div>
              </div>
            {/crmRegion}
          </div> <!-- end of left side -->
        </div>
      </div>
    <div class="clear"></div>
  </div>
  <div class="clear"></div>
</div><!-- /.crm-content-block -->

  <script type="text/javascript">
    {literal}
    cj(function($) {
      //explicitly stop spinner
      function stopSpinner( ) {
        $('li.crm-tab-button span').text(' ');
      }
      {/literal}
      var selectedTab = '{if !empty($selectedChild)}{$selectedChild}{else}summary{/if}';
      var tabIndex = $('#tab_' + selectedTab).prevAll().length;
      var spinnerImage = '<img src="{$config->resourceBase}i/loading.gif" style="width:10px;height:10px"/>';
      {literal}
      $("#mainTabContainer").tabs({ selected: tabIndex, spinner: spinnerImage, cache: true, load: stopSpinner});
      $(".crm-tab-button").addClass("ui-corner-bottom");
      $().crmAccordions();

      $('body').click(function() {
        cj('#crm-contact-actions-list').hide();
      });
    });
    {/literal}
  </script>
{/if}

{* CRM-10560 *}
{literal}
<script type="text/javascript">
cj(document).ready(function($) {
  $('.crm-inline-edit-container').crmFormContactLock({
    ignoreLabel: "{/literal}{ts escape='js'}Ignore{/ts}{literal}",
    saveAnywayLabel: "{/literal}{ts escape='js'}Save Anyway{/ts}{literal}",
    reloadLabel: "{/literal}{ts escape='js'}Reload Page{/ts}{literal}"
  });
  //Enhance styling of "View Contact" tabs to indicate empty/non-empty tags
  $('div#mainTabContainer ul').find('li').each(function(n){
    if($(this).find('em').html()==0){
      $(this).addClass("disabled");
    }
  });
});
</script>
{/literal}

{* jQuery validate *}
{include file="CRM/Form/validate.tpl" form=0}
