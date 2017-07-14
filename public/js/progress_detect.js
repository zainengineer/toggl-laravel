ProgressDetect = {};
ProgressDetect.duplicateHashes = {};
ProgressDetect.resolvedDates = {};
ProgressDetect.flagDuplicateHashes = () => {
    let $entries = $('.time-entry.zhash');
    $entries.removeClass('red');
    $entries.each(function (index) {
        let hash = $(this).data('zhash');
        let $zhash = $('.zhash-' + hash);
        if ($zhash.length > 1) {
            ProgressDetect.duplicateHashes[hash] = hash;
            $zhash.addClass('red')
        }
    });
    return ProgressDetect.duplicateHashes;
};
ProgressDetect.addWorkLogHashes = () => {
    let $entries = $('.time-entry.zhash');
    $entries.each(function (index) {
        let $this = $(this);
        let started = $this.data('started');
        let startedDate = ProgressDetect.resolvedDates[started];
        let comment = $this.data('comment');
        let timeSpent = $this.data('time-spent');
        let combineJson = {comment:comment,timeSpent:timeSpent,startedDate:startedDate};
        $this.data('zhash2-combine',combineJson);
        let zhash2 = sha1(JSON.stringify(combineJson));
        $this.data('zhash2',zhash2);
        $this.addClass('zhash2-' + zhash2);
    });
};

ProgressDetect.addTogglHashes = () => {
    let $entries = $('.jira-send-button');
    $entries.each(function (index) {
        let $this = $(this);
        let json = $this.data('time-entry');
        let comment = json.jira_entry;
        let timeSpent = json.jira_time;
        let timeStamp = json.unix_stamp;
        let startedDate = new Date(timeStamp * 1000);
        let combineJson = {comment:comment,timeSpent:timeSpent,startedDate:startedDate};
        let zhash2 = sha1(JSON.stringify(combineJson));
        $this.data('zhash2-combine',combineJson);
        $this.data('zhash2',zhash2);
        $this.addClass('zhash2-' + zhash2);
    });
};
ProgressDetect.compareTogglJira = () => {
    let $entries = $('.time-entry.zhash');
    $entries.each(function (index) {
        let $this = $(this);
        let zhash2  = $this.data('zhash2');
        let $send = $('.jira-send-button.zhash2-' +zhash2);
        if ($send.length){
            $send.addClass('grey');
        }

    });
};
ProgressDetect.strToTime = async(stringDate) => {
    let config = {
        url: "/strtotime.php",
        type: "get", //send it through get method
        data: {
            date: stringDate,
        },
    };
    let output = await Promise.resolve(jQuery.when($.ajax(config)));
    ProgressDetect.resolvedDates[stringDate] = new Date(output * 1000);
    return ProgressDetect.resolvedDates[stringDate];
};
ProgressDetect.resolveAllDates = async () => {
    ProgressDetect.restoreFromCache();
    let $entries = $('.time-entry.zhash');
    var DateList = {};
    $entries.each(function (index) {
        let started = $(this).data('started');
        //already value present
        if (ProgressDetect.resolvedDates.hasOwnProperty(started)){
            return true;
        }
        // let comment = $(this).data('comment');
        // let timeSpent = $(this).data('timeSpent');
        DateList[started] = started;
    });

    let PromiseList = [];
    for (let i in DateList) {
        PromiseList.push(ProgressDetect.strToTime(i))
    }
    return Promise.all(PromiseList);
};
ProgressDetect.saveDatesInCache = function () {
    ZStorage.saveObject('strtotime',ProgressDetect.resolvedDates);
};
ProgressDetect.restoreFromCache = function () {
    if (!$.isEmptyObject(ProgressDetect.resolvedDates)) {
        return;
    }
    let strtotime = ZStorage.getObject('strtotime');
    if (strtotime) {
        ProgressDetect.resolvedDates = strtotime;
    }
};
ProgressDetect.trackEntered = ()=>{
    let allPromise =ProgressDetect.resolveAllDates().then(()=>{
        ProgressDetect.saveDatesInCache();
        ProgressDetect.addWorkLogHashes();
        ProgressDetect.compareTogglJira();
    });
    ProgressDetect.addTogglHashes();
    return allPromise;
};