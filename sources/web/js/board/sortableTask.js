/*

Copyright (C) 2015 Hamza Ayoub, Valentin Chareyre, Sofian Hamou-Mamar, 
Alain Krok, Wenlong Li, Rémi Patrizio, Yamine Zaidou

________________________________

This file is part of Pilote.

    Pilote is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Pilote is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Pilote.  If not, see <http://www.gnu.org/licenses/>.

*/

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
                url: Routing.generate('pilote_tasker_moveTask'),
                data: { 'movedTaskId' : taskId, 'upperTaskId' : upperTaskId, 'newListId' : newListId },
                cache: false
            });
        }
    });
    $( ".task" ).disableSelection();

    $( ".tab-pane" ).sortable({
        items: ".taskList",
        handle: ".tList-heading",
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
                url: Routing.generate('pilote_tasker_moveList'),
                data: { 'movedListId' : tListId, 'leftListId' : leftListId },
                cache: false
            });
        }
    });
};

setSortableTask();