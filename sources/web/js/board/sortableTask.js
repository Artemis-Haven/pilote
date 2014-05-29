/**
 * Rend les tâches et les listes de tâches triables
 */
function setSortableTask() {
    $( ".sortableTasksContainer" ).sortable({
        connectWith: ".sortableTasksContainer",
        items: ".task , .blankTask",
        handle: ".task-header",
        placeholder: "task-placeholder",
        revert:true,
        update: function(event, ui) {
            taskId = ui.item.attr('id').replace('task-', '');
            upperTask = ui.item.prev('.task');
            newListId = 0;
            if (upperTask.length == 0)
            {
                upperTaskId = -1;
                newListId = ui.item.parents('.taskList').attr('id').replace('tList-', '');
            }
            else
            {
                upperTaskId = upperTask.attr('id').replace('task-', '');
            }
            
            $.ajax({
                type: "POST",
                dataType:"json",
                url: Routing.generate('piltasker_moveTask'),
                data: { 'movedTaskId' : taskId, 'upperTaskId' : upperTaskId, 'newListId' : newListId },
                cache: false
            });
        }
    });
    $( ".task" ).disableSelection();

    $( ".tab-pane" ).sortable({
        items: ".taskList",
        handle: ".panel-heading",
        revert:true,
        update: function(event, ui) {
            tListId = ui.item.attr('id').replace('tList-', '');
            leftList = ui.item.prev('.taskList');
            if (leftList.length == 0)
                leftListId = -1;
            else
                leftListId = leftList.attr('id').replace('tList-', '');

            $.ajax({
                type: "POST",
                dataType:"json",
                url: Routing.generate('piltasker_moveList'),
                data: { 'movedListId' : tListId, 'leftListId' : leftListId },
                cache: false
            });
        }
    });
};

setSortableTask();