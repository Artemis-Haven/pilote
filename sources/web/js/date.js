function date_heure() {
        today = new Date;
        jour = today.getDay();
        date = today.getDate();
        mois = today.getMonth()+1;
        annee = today.getFullYear();
        heures= today.getHours();
        minutes = today.getMinutes();

         if(heures<10)
    {
            heures = "0"+heures;
    }
    if(minutes<10)
    {
            minutes = "0"+minutes;
    }
    if(mois<10)
    {
            mois = "0"+mois;
    }
    if(date<10)
    {
            date = "0"+date;
    }

        var jours=new Array("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");

        document.getElementById('date').innerHTML = jours[jour]+' '+date+"/"+mois+" - "+ heures + "h" + minutes;

        setTimeout('date_heure();','1000');
}