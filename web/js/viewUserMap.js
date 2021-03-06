var map2;
var allMarkers = [];
var markers;

    function initMap() {

        var france = {lat: 46.460374, lng: 2.232049};

        map2 = new google.maps.Map(document.getElementById('map-view'), {
            zoom: 5,
            maxZoom: 11,
            center: france
        });
    }

    function toggleMarkers() {
        for (i = 0; i < allMarkers.length; i++) {
            allMarkers[i].setMap(null);

        }
        allMarkers = [];
    }

    // AFFICHAGE DES MARKER D'OBS SUR LA MAP

    // supprime les markercluster si il y en a
    var markerCluster;
    $('.filtre-td').click(function(){
        if(!markerCluster){


            markerCluster = new MarkerClusterer(map2, allMarkers,
                {
                    imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});
        }else{
            markerCluster.clearMarkers();
            markerCluster = new MarkerClusterer(map2, allMarkers,
                {
                    imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});
        }

    });



    // affiche les marqueur de l'espèce choisie sur la map
    $('.filtrer').click(function(){

        var row = $(this).closest("tr");
        var longitudes = row.find(".longitude p");
        var latitudes = row.find(".latitude p");
        var viewObsId = row.find(".obsId p");
        var dateObs = row.find('.obsDate p');
        var i;


        // INFOWINDOWS SUR LES MARKERS
        function addInfo(){
            var urlViewObs = Routing.generate('viewObservation', {'id': viewObsId[i].textContent});
            var contentString =
                '<h4 class="titre-infowindow"> Observation</h4>' +
                '<p class="text-infowindow">Date de l\'observation : '+ dateObs[i].textContent +' </p>' +
                '<p><a class="pathObs" href="'+ urlViewObs +'"> Voir cette observation</a></p>';

            var infowindow = new google.maps.InfoWindow({
                content: contentString
            });

            google.maps.event.addListener(markers,'click', (function(markers,content,infowindow){

                return function() {
                    infowindow.close();
                    infowindow.open(map2, markers);

                };
            })(markers,contentString,infowindow));
        }


        // =================================

        toggleMarkers();

        if (markers){
            for (i = 0; i < latitudes.length && i < longitudes.length; i++)
            {

                markers = new google.maps.Marker({
                    animation: google.maps.Animation.DROP,
                    position: {lat: parseFloat(latitudes[i].textContent),
                        lng: parseFloat(longitudes[i].textContent)
                    },
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 30,
                        fillColor: 'red',
                        fillOpacity: 0.5,
                        strokeWeight: 1,
                        strokeColor: 'red'
                    },
                    map: map2
                });

                map2.setZoom(5);
                allMarkers.push(markers);
                addInfo();
            }

        }else{

            for (i = 0; i < latitudes.length && i < longitudes.length; i++)
            {

                markers = new google.maps.Marker({
                    animation: google.maps.Animation.DROP,
                    position: {lat: parseFloat(latitudes[i].textContent),
                        lng: parseFloat(longitudes[i].textContent)
                    },
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 30,
                        fillColor: 'red',
                        fillOpacity: 0.5,
                        strokeWeight: 1,
                        strokeColor: 'red'
                    },
                    map: map2
                });
                allMarkers.push(markers);

                addInfo();
            }
        }

    });

