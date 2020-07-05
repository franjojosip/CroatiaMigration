<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prirodno Kretanje Stanovništva</title>
    <link rel="stylesheet" type="text/css" href="style/index.css" media="screen" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="shortcut icon" href="images/main.png">
    <script src="scripts/d3.js"></script>
    <script src="scripts/jquery.js"></script>
    <script src="scripts/topojson.js"></script>
</head>

<body onload="sizeChange()">
    <header>
        <h1>
            Prirodno kretanje stanovništva u Hrvatskoj
        </h1>
    </header>
    <div id="container">
        <div class="row">
            <div class="column" id="colMap">
                <button type="button" class="btn btn-dark btnReset" onClick="resetZoom()">Reset zoom</button>
                <div class="dropdown">
                    <button type="button" class="btn btn-info dropdown-toggle">
                        Tip migracije
                    </button>
                    <div class="dropdown-menu">
                        <button class="dropdown-item" type="button"
                            onClick="changeType('doseljeni_ukupno')">Doseljavanje</button>
                        <button class="dropdown-item" type="button"
                            onClick="changeType('odseljeni_ukupno')">Odseljavanje</button>
                    </div>
                </div>
                <div class="dropdown">
                    <button type="button" class="btn btn-info dropdown-toggle">
                        Godina
                    </button>
                    <div class="dropdown-menu">
                        <button class="dropdown-item" type="button" onClick="changeYear(2010)">2010</button>
                        <button class="dropdown-item" type="button" onClick="changeYear(2011)">2011</button>
                        <button class="dropdown-item" type="button" onClick="changeYear(2012)">2012</button>
                        <button class="dropdown-item" type="button" onClick="changeYear(2013)">2013</button>
                        <button class="dropdown-item" type="button" onClick="changeYear(2014)">2014</button>
                        <button class="dropdown-item" type="button" onClick="changeYear(2015)">2015</button>
                        <button class="dropdown-item" type="button" onClick="changeYear(2016)">2016</button>
                    </div>
                </div>
                <button type="button" class="btn btn-success" id="btnAnimation" onClick="startStop(0)">Pokreni
                    animaciju</button>
                <h3 id="currentMigration"></h3>
                <script>

                    //Sirina i visina svg prostora
                    //Lock varijabla za onemogucavanje klikanja pokreni ako je vec pokrenuta animacija
                    //Places ce sadrzavati mjesta za odabranu godinu
                    var width = window.screen.width,
                        height = window.screen.height,
                        lock = false, places;
                    var colors = ["#F6E0A9", "#F29F4A", "#CC0404", "#6C0303"];

                    var years = [2010, 2011, 2012, 2013, 2014, 2015, 2016];

                    // Define the div for the tooltip
                    var div = d3.select("body").append("div")
                        .attr("class", "tooltip")
                        .style("opacity", 0);

                    //Dohvacanje vrijednosti iz lokalnog spremnika na pregledniku i provjera te postavljanje defaultnih vrijednosti
                    var year = window.localStorage.getItem("year");
                    var type = window.localStorage.getItem("type");

                    if (year == null) {
                        window.localStorage.setItem("year", 2010);
                        year = 2010;
                    }
                    if (type == null) {
                        window.localStorage.setItem("type", "odseljeni_ukupno");
                        type = "odseljeni_ukupno";
                    }

                    //Inicijalizacije karte
                    setTitle();
                    setMap(year);

                    //Postavljanje opsega zumiranja karte
                    const zoom = d3.zoom()
                        .scaleExtent([1, 13])
                        .on("zoom", zoomed);

                    //Kreiranje projekcije
                    var projection = d3.geoMercator()
                        .scale(6850)
                        .rotate([-180, 0])
                        .center([0, 10])
                        .translate([width * 10.58, height * 4.78]);

                    var path = d3.geoPath()
                        .projection(projection);

                    //Kreiraj svg prostor za dodavanje karte
                    var svg = d3.select("#colMap")
                        .append("svg")            
			.attr("viewBox", [0, 0, width, height])
                        .attr("class", "map")
                        .append("g")

                    svg.call(zoom);
                    var g = svg.append("g");

                    //Ucitaj topoJson Hrvatske
                    d3.json("cro_regv3.json", function (error, cro) {
                        var data = topojson.feature(cro, cro.objects.layer1);
                        g.selectAll("path.county")
                            .data(data.features)
                            .enter()
                            .append("path")
                            .attr("class", "county")
                            .attr("id", function (d) { return d.id; })
                            .attr("d", path)
                            .style("stroke", "white")
                            .style("stroke-width", 0.3)
                            .on("click", clicked)
                            .on("mouseover", function (d) {
                                div.transition()
                                    .duration(300)
                                    .style("opacity", 1);
                                div.html("Klikni za informacije o županiji")
                                    .style("left", (d3.event.pageX) + "px")
                                    .style("top", (d3.event.pageY - 28) + "px");
                            })
                            .on("mouseout", function (d) {
                                div.transition()
                                    .duration(300)
                                    .style("opacity", 0);
                            });
                    })

                    d3.select(window)
                        .on("resize", sizeChange);

                    function setMap(year) {

                        d3.json("kretanje_stanovnistva.json", function (error, data) {
                            //Places sadrzava mjesta i podatke prema odabranoj godini
                            places = data.filter(function (place) {
                                return place.godina == year;
                            })

                            //Uzima se najveca vrijednost ovisno o odabranom tipu migracije
                            var max = d3.max(places, function (d) { return d[type] });

                            //Skriva se info tab o gradu i zupaniji jer nisu jos odabrani
                            (document.getElementsByClassName("zupanija_info"))[0].style.display = "none";
                            (document.getElementsByClassName("grad_info"))[0].style.display = "none";

                            //Skala koja prima domenu od 0 do najvece vrijednosti tipa migracije
                            //Te pretvara u jedan broj iz opsega 0-3 koji ujedno predstavljaju index opsega broja migracija
                            var indexScale = d3.scaleQuantize()
                                .domain([0, max])
                                .range([0, 1, 2, 3])

                            //Racunanje i postavljanje pojedinog opsega broja migracija
                            document.getElementById("firstRange").innerHTML = Math.round(indexScale.invertExtent(3)[0]) + " - " + Math.round(indexScale.invertExtent(3)[1]);
                            document.getElementById("secondRange").innerHTML = Math.round(indexScale.invertExtent(2)[0]) + " - " + (Math.round(indexScale.invertExtent(2)[1]) - 1);
                            document.getElementById("thirdRange").innerHTML = Math.round(indexScale.invertExtent(1)[0]) + " - " + (Math.round(indexScale.invertExtent(1)[1]) - 1);
                            document.getElementById("fourthRange").innerHTML = "0 - " + Math.round(indexScale.invertExtent(0)[1]);

                            //Skala koja ovisno o dobivenom broju vraca vrijednost za velicinu kruznice u rangu 1 do 20 px
                            var circleScale = d3.scaleLinear()
                                .domain([0, max])
                                .range([1, 20]);

                            //Kreiranje kvadratica i oznacavanje svako mjesta na karti
                            g.selectAll("rect")
                                .data(places)
                                .enter()
                                .append("rect")
                                .attr("x", function (d) {
                                    return projection([parseFloat(d.lng), parseFloat(d.lat)])[0];
                                })
                                .attr("y", function (d) {
                                    return projection([parseFloat(d.lng), parseFloat(d.lat)])[1];
                                })
                                .attr("height", 1.7)
                                .attr("width", 1.7)
                                .style("fill", "black")
                                .style("opacity", 0.95)

                            //Dodjeljivanje kruzica ovisno o broju ljudi za odabran tip i godinu migracije, velicina se dobiva iz skale koja ovisi broju ljudi
                            g.selectAll("circle")
                                .data(places)
                                .enter()
                                .append("circle")
                                .attr("cx", function (d) {
                                    return projection([parseFloat(d.lng) + 0.0064, parseFloat(d.lat)])[0];
                                })
                                .attr("cy", function (d) {
                                    return projection([parseFloat(d.lng), parseFloat(d.lat) - 0.005])[1];
                                })
                                .attr("r", function (d) {
                                    return circleScale(d[type]);
                                })
                                .style("fill", function (d) {
                                    return colors[indexScale(d[type])];
                                })
                                .style("opacity", 0.4)
                                .style("stroke", "black")
                                .style("stroke-width", 0.1)
                                .on("click", clickCity)
                                .on("mouseover", function (d) {
                                    div.transition()
                                        .duration(200)
                                        .style("opacity", 1);
                                    div.html("Klikni za informacije (" + d.mjesto + ")")
                                        .style("left", (d3.event.pageX) + "px")
                                        .style("top", (d3.event.pageY - 28) + "px");
                                })
                                .on("mouseout", function (d) {
                                    div.transition()
                                        .duration(300)
                                        .style("opacity", 0);
                                });
                        });
                    }

                    //Fja pri kliku na grad, prikazuje info tab o gradu, a skriva onaj o zupaniji
                    function clickCity(d) {
                        (document.getElementsByClassName("zupanija_info"))[0].style.display = "none";
                        (document.getElementsByClassName("grad_info"))[0].style.display = "";
                        document.getElementById("grad_info_zupanija").innerHTML = d.zupanija;
                        document.getElementById("grad_info_mjesto").innerHTML = d.mjesto;
                        document.getElementById("grad_info_doseljeno").innerHTML = d.doseljeni_ukupno;
                        document.getElementById("grad_info_odseljeno").innerHTML = d.odseljeni_ukupno;
                    }

                    //Fja pri kliku na zupaniju, prikazuje info tab o zupaniji, a skriva onaj o gradu
                    //Uz to priblizava kartu za bolji pregled zupanije i gradova
                    //Priblizavanje i dimenzije ovise o velicini prozora
                    function clicked(d) {
                        const [[x0, y0], [x1, y1]] = path.bounds(d);
                        d3.event.stopPropagation();
                        if ($("#colMap").width() > 1000) {
                            svg.transition().duration(850).call(
                                zoom.transform,
                                d3.zoomIdentity
                                    .translate($("#colMap").width() / 7, $("#colMap").height() / 2.3)
                                    .scale(Math.min(4, 0.9 / Math.max((x1 - x0) / $("#colMap").width() * 0.758, (y1 - y0) / $("#colMap").height())))
                                    .translate(-(x0 + x1) / 2.34, -(y0 + y1) / 2.1),
                                d3.mouse(svg.node())
                            );
                        }
                        else if ($("#colMap").width() > 800) {
                            svg.transition().duration(850).call(
                                zoom.transform,
                                d3.zoomIdentity
                                    .translate($("#colMap").width() / 5.5, $("#colMap").height() / 1)
                                    .scale(Math.min(14, 0.9 / Math.max((x1 - x0) / $("#colMap").width() * 5.138, (y1 - y0) / $("#colMap").height())))
                                    .translate(-(x0 + x1) / 7.2, -(y0 + y1) / 1.7),
                                d3.mouse(svg.node())
                            );
                        }
                        else if ($("#colMap").width() > 600) {
                            svg.transition().duration(850).call(
                                zoom.transform,
                                d3.zoomIdentity
                                    .translate($("#colMap").width() / 25, $("#colMap").height() / 14.3)
                                    .scale(Math.min(14, 1.9 / Math.max((x1 - x0) / $("#colMap").width() / 4.5, (y1 - y0) / $("#colMap").height())))
                                    .translate(-(x0 + x1) / 2.36, -(y0 + y1) / 2.3121),
                                d3.mouse(svg.node())
                            );
                        }
                        else if ($("#colMap").width() > 500) {
                            svg.transition().duration(850).call(
                                zoom.transform,
                                d3.zoomIdentity
                                    .translate($("#colMap").width() / 25, $("#colMap").height() / 17.3)
                                    .scale(Math.min(5, 1.9 / Math.max((x1 - x0) / $("#colMap").width() / 4.5, (y1 - y0) / $("#colMap").height())))
                                    .translate(-(x0 + x1) / 2.6159, -(y0 + y1) / 2.81),
                                d3.mouse(svg.node())
                            );
                        }
                        else {
                            svg.transition().duration(850).call(
                                zoom.transform,
                                d3.zoomIdentity
                                    .translate($("#colMap").width() / 25, $("#colMap").height() / 17.3)
                                    .scale(Math.min(5, 1.9 / Math.max((x1 - x0) / $("#colMap").width() / 4.5, (y1 - y0) / $("#colMap").height())))
                                    .translate(-(x0 + x1) / 2.4159, -(y0 + y1) / 2.65),
                                d3.mouse(svg.node())
                            );
                        }
                        d3.selectAll('path').style('fill', "#115a99a4");
                        d3.select(this).style("fill", "#197cb65d");
                        document.getElementById("zupanija_info_naziv").innerHTML = d.properties.gn_name;
                        document.getElementById("zupanija_info_egradani").innerHTML = d.properties.broj_korisnika;
                        document.getElementById("zupanija_info_broj_gradova").innerHTML = countCities(d.properties.gn_name);
                        document.getElementById("zupanija_info_doseljeno").innerHTML = countDoseljeno(d.properties.gn_name);
                        document.getElementById("zupanija_info_odseljeno").innerHTML = countOdseljeno(d.properties.gn_name);
                        (document.getElementsByClassName("zupanija_info"))[0].style.display = "";
                        (document.getElementsByClassName("grad_info"))[0].style.display = "none";
                    }

                    //Promjena velicine containera ovisno o velicini internet preglednika
                    function sizeChange() {
                        if ($("#container").width() > 1200) {
                            d3.select("g").attr("transform", "scale(" + $("#container").width() / 2200 + ")");
                            $("svg").height($("#container").width() * 0.37);
                        }
                        else if ($("#container").width() < 1200 && $("#container").width() > 800) {
                            d3.select("g").attr("transform", "scale(" + $("#container").width() / 2900 + ")");
                            $("svg").height($("#container").width() * 0.438);
                        }
                        else {
                            d3.select("g").attr("transform", "scale(" + $("#container").width() / 1500 + ")");
                            $("svg").height($("#container").width() * 0.538);
                        }
                    }

                    //Funkcija za zumiranje prilikom skrolanja misem
                    function zoomed() {
                        const { transform } = d3.event;
                        g.attr("transform", transform);
                        g.attr("stroke-width", 1 / transform.k);
                    }

                    //Resetiranje zooma te centriranje karte
                    function resetZoom() {
                        svg.transition().duration(750).call(zoom.transform, d3.zoomIdentity);
                        d3.selectAll('path').style('fill', null);
                        (document.getElementsByClassName("column-xs-3 zupanija_info"))[0].style.display = "none";
                        (document.getElementsByClassName("column-xs-3 grad_info"))[0].style.display = "none";
                    }

                    //Prebrojavanje koliko ima gradova u danoj zupaniji
                    function countCities(zupanija) {
                        return (places.filter(function (place) {
                            return place.zupanija == zupanija;
                        })).length;
                    }

                    //Prebrojavanje koliko ima doseljenih u danoj zupaniji
                    function countDoseljeno(zupanija) {
                        var sumaDoseljenih = 0;
                        var gradovi = places.forEach(function (place) {
                            if (place.zupanija == zupanija) {
                                sumaDoseljenih += place.doseljeni_ukupno;
                            }
                        });
                        return sumaDoseljenih;
                    }

                    //Prebrojavanje koliko ima odseljenih u danoj zupaniji
                    function countOdseljeno(zupanija) {
                        var sumaOdseljenih = 0;
                        var gradovi = places.filter(function (place) {
                            if (place.zupanija == zupanija) {
                                sumaOdseljenih += place.odseljeni_ukupno;
                            }
                            return place.zupanija == zupanija;
                        });
                        return sumaOdseljenih;
                    }

                    //Promjena vrijednosti tipa migracije u lokalnom spremniku na internet pregledniku i refreshanje mape
                    function changeType(type) {
                        window.localStorage.setItem("type", type);
                        reset();
                    }

                    //Promjena vrijednosti godine u lokalnom spremniku na internet pregledniku i refreshanje mape
                    function changeYear(year) {
                        window.localStorage.setItem("year", year);
                        reset();
                    }

                    //Pokretanje animacije koja prolazi kroz sve godine i prikazuje vrijednosti na karti
                    function startStop(index) {
                        if (lock && index == 0) {
                            return;
                        }
                        else {
                            lock = true;
                            document.getElementById("btnAnimation").disabled = true;
                            setTimeout(function () {
                                if (index < years.length) {
                                    g.selectAll("circle").remove();
                                    setMap(years[index]);
                                    console.log(years[index]);

                                    let type = window.localStorage.getItem("type");
                                    if (type == "odseljeni_ukupno") {
                                        document.getElementById("currentMigration").innerHTML = "Odseljeni - " + years[index] + ". godina";
                                    }
                                    else {
                                        document.getElementById("currentMigration").innerHTML = "Doseljeni - " + years[index] + ". godina";
                                    }

                                    index++;
                                    startStop(index);
                                }
                                else {
                                    reset();
                                }
                            }, 1400);
                        }
                    }

                    //Postavljanje naslova prema odabranom tipu i godini migracije
                    function setTitle() {
                        if (window.localStorage.getItem("type") == "odseljeni_ukupno") {
                            document.getElementById("currentMigration").innerHTML = "Odseljeni - " + window.localStorage.getItem("year") + ". godina";
                        }
                        else {
                            document.getElementById("currentMigration").innerHTML = "Doseljeni - " + window.localStorage.getItem("year") + ". godina";
                        }
                    }

                    //Resetiranje vrijednosti na mapi na defaultne, tj. one koje su spremljene u lokalnom spremniku preglednika
                    function reset() {
                        let year = window.localStorage.getItem("year");
                        type = window.localStorage.getItem("type");
                        setTitle();
                        g.selectAll("circle").remove();
                        setMap(year);
                        lock = false;
                        document.getElementById("btnAnimation").disabled = false;
                    }

                    function routeInfoPage() {
                        var zupanija = document.getElementById("zupanija_info_naziv").innerHTML;
                        window.localStorage.setItem("zupanija", zupanija);
                        window.location = "info.php"
                    }

                </script>
            </div>
            <div class="column-xs-3 legenda">
                <h3>Legenda</h3>
                <ul class="list-group">
                    <h6 style="margin-top:10px">Oznake</h6>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Grad ili općina
                        <span class="rect" style="height:15px;width:15px;background-color: #030303;"></span>
                    </li>
                    <h6 style="margin-top:10px">Broj migracija</h6>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span id="firstRange"></span>
                        <span class="dot" style="height:30px;width:30px;background-color: #6C0303;"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span id="secondRange"></span>
                        <span class="dot" style="height:25px;width:25px;background-color: #CC0404;"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span id="thirdRange"></span>
                        <span class="dot" style="height:20px;width:20px;background-color: #F29F4A;"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span id="fourthRange"></span>
                        <span class="dot" style="height:15px;width:15px;background-color: #F6E0A9;"></span>
                    </li>
                </ul>

            </div>
            <div class="column-xs-3 zupanija_info">
                <h3 class="info">Informacije o županiji</h3>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 style="margin-top:10px">Naziv</h6>
                        <span id="zupanija_info_naziv"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 style="margin-top:10px">Broj E-građana</h6>
                        <span id="zupanija_info_egradani"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 style="margin-top:10px">Broj većih gradova ili općina</h6>
                        <span id="zupanija_info_broj_gradova"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 style="margin-top:10px">Ukupno odseljeno</h6>
                        <span id="zupanija_info_odseljeno"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 style="margin-top:10px">Ukupno doseljeno</h6>
                        <span id="zupanija_info_doseljeno"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 style="margin-top:10px">Prikaz grafa za županiju</h6>
                        <button type="button" class="btn btn-outline-primary" onClick="routeInfoPage()">Prikaži</button>
                    </li>
                </ul>
            </div>
            <div class="column-xs-3 grad_info">
                <h3 class="info">Informacije o gradu</h3>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 style="margin-top:10px">Županija</h6>
                        <span id="grad_info_zupanija"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 style="margin-top:10px">Naziv grada</h6>
                        <span id="grad_info_mjesto"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 style="margin-top:10px">Ukupno odseljeno</h6>
                        <span id="grad_info_odseljeno"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 style="margin-top:10px">Ukupno doseljeno</h6>
                        <span id="grad_info_doseljeno"></span>
                    </li>
                </ul>
            </div>
        </div>

        <footer>
            Copyright @FJJukic 2020
        </footer>
</body>

</html>