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

$(function () {

    /**************************/
    /* Configuration du Gantt */
    /**************************/

    // Création du nouveau type "myMilestone", représentant un jalon 
    gantt.config.types.myMilestone = "myMilestone";
    gantt.locale.labels.myMilestone = "Milestone";
    // Ajouter la classe "gantt_milestone" aux tâches
    // de type "milestone"
    gantt.templates.task_class = function(start, end, task){
        if(task.type == gantt.config.types.myMilestone){
            return "myMilestone";
        }
        return "";
    };
    // Masquer le texte des tâches de type "milestone"
    gantt.templates.task_text = function(start, end, task){
        if(task.type == gantt.config.types.myMilestone){
            return "";
        }
        return task.text;
    };
    //Afficher le texte à droite des tâches de type "milestone"
    gantt.templates.rightside_text = function (start, end, task) {
        if (task.type == "myMilestone") {
            return task.text;
        };
        return "";
    }

    // Corriger le texte de la popup lors de la suppression d'un lien
    gantt.templates.link_description = function (link) {
        return " de <b>"+gantt.getTask(link.source).text+"</b> à <b>"+gantt.getTask(link.target).text+"</b> ";
    }

    // Surcharge de la fonction affichant des popups
    // permettant ainsi la traduction en français du
    // bouton "Annuler"
    gantt._dhtmlx_confirm = function(message, title, callback, ok) {
        if (!message)
            return callback();
        var opts = { text: message };
        if (title)
            opts.title = title;
        if(ok){
            opts.ok = ok;
        }
        if (callback) {
            opts.callback = function(result) {
                if (result)
                    callback();
            };
        }
        opts.cancel = "Annuler";
        dhtmlx.confirm(opts);
    };

    // Empêcher de changer la progression de toutes les tâches
    gantt.config.drag_progress = false;

    // Empecher l'ouverture de la lightbox (la popup de détail des tâches
    // de dhtmlxgantt par défaut) et ouvrir la nôtre uniquement si c'est 
    // une tâche, pas un projet
    gantt.attachEvent("onBeforeLightbox", function(id, mode, e){
        if(gantt.getTask(id).type == gantt.config.types.task ||
            gantt.getTask(id).type == gantt.config.types.myMilestone ){
            getTaskDetails(id.substring(1));
            $("#modalTask").modal("show");
        }
        return false;
    });

    // Définir les différentes colonnes apparaissant à gauche du Gantt
    // Par défaut, on a les colonnes suivantes : Texte de la tâche, 
    // Date de départ de la tâche, Durée de la tâche, Ajouter une tâche fille.
    gantt.config.grid_width = 400;
    gantt.config.columns = [
        {name:"text",        width:"*", tree:true },
        {name:"start_date", align: "center", width:85, template: function (t) {
            var formatFunc = gantt.date.date_to_str("%d %M %Y");
            if (t.type == "myMilestone")
                return "-";
            else
                return formatFunc(t.start_date);
        }},
        {name:"end_date", label:"Date finale", align: "center", width:85, template: function(t) {
            var formatFunc = gantt.date.date_to_str("%d %M %Y");
            return formatFunc(gantt.date.add(t.end_date, -1, 'day'));
        }},
    ];

    // Griser les weekends, à la fois dans la barre du haut (scale_cell) et
    // dans le tableau central (task_cell)
    gantt.templates.scale_cell_class = function(date){
        if( (date.getDay()==0||date.getDay()==6) && gantt.config.scale_unit == "day" ) {
            return "weekend";
        }    
    };
    gantt.templates.task_cell_class = function(item, date){
        if( (date.getDay()==0||date.getDay()==6) && gantt.config.scale_unit == "day" ) {
            return "weekend";
        }    
    };

    // Changer la légende de l'échelle de temps, en fonction de l'échelle
    // (3 choix : jours, semaines ou mois)
    gantt.config.date_scale = null;
    gantt.templates.date_scale = function (date) {
        var dateToStr = gantt.date.date_to_str("%d %M");
        if  (gantt.config.scale_unit == "week") {
            var endDate = gantt.date.add(gantt.date.add(date, 1, "week"), -1, "day");
            return dateToStr(date) + " - " + dateToStr(endDate);
        } else if (gantt.config.scale_unit == "month") {
            dateToStr = gantt.date.date_to_str("%F %Y");
            return dateToStr(date);
        } else { // scale_unit : day
            return dateToStr(date);
        }
    }

    /**************************/
    /* Gestion des événements */
    /**************************/

    // Empecher de redimensionner un jalon
    gantt.attachEvent("onBeforeTaskDrag", function(id, mode, e){
        if (this.getTask(id).type == "milestone" && mode == "resize") return false;
        return true;
    });

    // Après un déplacement ou un redimensionnement de tâche,
    // on envoie les nouvelles dates de début et de fin à la base
    // de données.
    gantt.attachEvent("onAfterTaskDrag", function(id, mode, e){
        if (mode != "resize" && mode != "move") return true;
        
        var task = this.getTask(id);
        var formatFunc = gantt.date.date_to_str("%d/%m/%Y");
        var startDate = formatFunc(task.start_date);
        
        if (task.type == "myMilestone") {
            var endDate = formatFunc(task.start_date);
            $.ajax({
                type: "POST",
                dataType:"json",
                url: Routing.generate('pilote_tasker_gantt_moveTask'),
                data: { 'taskId' : id.substring(1), 'startDate' : null, 'endDate': endDate },
                cache: false,
            });
        } else if (task.type == "task") {
            if (task.start_date >= task.end_date) {
                task.end_date = gantt.date.add(task.end_date, 1, 'day');
            }
            var endDate = formatFunc(gantt.date.add(task.end_date, -1, 'day'));
            $.ajax({
                type: "POST",
                dataType:"json",
                url: Routing.generate('pilote_tasker_gantt_moveTask'),
                data: { 'taskId' : id.substring(1), 'startDate' : startDate, 'endDate': endDate },
                cache: false,
            });
        }

    });

    // Empecher les liens avec les projets
    gantt.attachEvent("onBeforeLinkAdd", function(id, link){
        if (gantt.getTask(link.source).type == "project" || gantt.getTask(link.target).type == "project") {
            return false;
        }
    });
    

    // Après chaque création de lien entre deux tâches,
    // on enregistre la création dans la base de données
    gantt.attachEvent("onAfterLinkAdd", function(id, link){
        $.ajax({
            type: "POST",
            dataType:"json",
            url: Routing.generate('pilote_tasker_gantt_addLink'),
            data: { 'source' : link.source.substring(1), 'target': link.target.substring(1), 'type': link.type },
            cache: false,
            success: function (data) {
                if (data['exists']) gantt.deleteLink(id);
            }
        });
        return true;
    });


    // Après chaque suppression de lien entre deux tâches,
    // on enregistre la suppression dans la base de données
    gantt.attachEvent("onAfterLinkDelete", function(id, link){
        $.ajax({
            type: "POST",
            dataType:"json",
            url: Routing.generate('pilote_tasker_gantt_deleteLink'),
            data: { 'source' : link.source.substring(1), 'target': link.target.substring(1), 'type': link.type },
            cache: false,
        });
        return true;
    });


});