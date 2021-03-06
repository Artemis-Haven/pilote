/*

Copyright (C) 2015 Rémi Patrizio

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
 * Scripts gérant le Calendrier
 */

/**
 * Au déplacement d'une tâche, effectuer l'action en base de données
 */
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

 