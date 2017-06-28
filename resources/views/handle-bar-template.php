<script id="html-work-log-entries" type="text/x-handlebars-template">
    <div class="time-entry">
        {{#each worklogs}}
            <span>{{created_at}}</span>
            <span>{{timeSpent}}</span>
            <span>{{comment}}</span>
        {{/each}}
    </div>
</script>

<script id="work-log-entries" type="text/x-handlebars-template">
{{#each worklogs}}{{created}} - {{timeSpent}} - {{comment}}<?php echo "\n"; ?>{{/each}}
</script>