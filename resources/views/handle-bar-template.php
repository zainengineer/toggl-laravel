<script id="work-log-entries" type="text/x-handlebars-template">
    <br/>
    <br/>
    {{#each worklogs}}
    <div class="time-entry row">
            <span class="col-1">{{timeSpent}}</span>
            <span class="col">{{started}}</span>
            <span class="col">{{comment}}</span>
            <span class="col-3">&nbsp;</span>
    </div>
    {{/each}}
    <br/>
</script>

<script id="pre-work-log-entries" type="text/x-handlebars-template">
{{#each worklogs}}{{created}} - {{timeSpent}} - {{comment}}<?php echo "\n"; ?>{{/each}}
</script>