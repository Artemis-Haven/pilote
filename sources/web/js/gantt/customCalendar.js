function moveEvent (task) {
    var startDate = moment(task.start).format("DD/MM/YYYY");
    var endDate = moment(task.end).subtract(1, 'days').format("DD/MM/YYYY");
    
    if ($.inArray("milestone", task.className) != -1) {
        startDate = null;
        endDate = moment(task.start).format("DD/MM/YYYY");
    }
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_gantt_moveTask'),
        data: { 'taskId' : task.id, 'startDate' : startDate, 'endDate': endDate },
        cache: false,
    });
}

 