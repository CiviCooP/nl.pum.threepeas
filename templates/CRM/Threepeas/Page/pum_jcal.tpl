{assign var='displayDate' value=$elementDate|cat:"_display"}
<input type="text" name="{$displayDate}" id="{$displayDate}" class="dateplugin" autocomplete="off"/>
<span class="crm-clear-link">(<a href="#" onclick="clearDateTime( '{$displayDate}' ); return false;">{ts}clear{/ts}</a>)</span>
{strip}
    <script type="text/javascript">
        {literal}cj( function() {{/literal}
            var element_date   = "#{$displayDate}";
            var currentYear = new Date().getFullYear();
            var alt_field   = '#{$elementDate}';
            cj( alt_field ).hide();
            var date_format = 'dd-mm-yy';
            var altDateFormat = 'dd-mm-yy';
            var yearRange   = currentYear - parseInt( cj( alt_field ).attr('startOffset') );
                yearRange  += ':';
                yearRange  += currentYear + parseInt( cj( alt_field ).attr('endOffset'  ) );
            {literal}
                var startRangeYr = currentYear - parseInt( cj( alt_field ).attr('startOffset') );
                var endRangeYr = currentYear + parseInt( cj( alt_field ).attr('endOffset'  ) );
                var lcMessage = {/literal}"{$config->lcMessages}"{literal};
                var localisation = lcMessage.split('_');
                var dateValue = cj(alt_field).val( );
                cj(element_date).datepicker({
                    closeAtTop        : true,
                    dateFormat        : date_format,
                    changeMonth       : true,
                    changeYear        : true,
                    altField          : alt_field,
                    altFormat         : altDateFormat,
                    yearRange         : yearRange,
                    regional          : localisation[0],
                    minDate           : new Date(startRangeYr, 1 - 1, 1),
                    maxDate           : new Date(endRangeYr, 12 - 1, 31)
                });
                // set default value to display field, setDefault param for datepicker
                // is not working hence using below logic
                // parse the date
                var displayDateValue = cj.datepicker.parseDate( altDateFormat, dateValue );
                // format date according to display field
                displayDateValue = cj.datepicker.formatDate( date_format, displayDateValue );
                cj( element_date).val( displayDateValue );
                cj(element_date).click( function( ) {
                    hideYear( this );
                });
                cj('.ui-datepicker-trigger').click( function( ) {
                    hideYear( cj(this).prev() );
                });
            });
            function hideYear( element ) {
                var format = cj( element ).attr('format');
                if ( format == 'dd-mm' || format == 'mm/dd' ) {
                    cj(".ui-datepicker-year").css( 'display', 'none' );
                }
            }
            function clearDateTime( element ) {
                cj('input#' + element + ',input#' + element + '_time' + ',input#' + element + '_display').val('');
            }
        {/literal}
    </script>
{/strip}

