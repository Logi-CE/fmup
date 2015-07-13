var timer;
var currentColonne = "";
var currentUniqueId = "";
var memoClickX = 0;
var tableau_info_colonne = new Array();
var memoIndiceCurrentColonne = 0;

function cocherTout (nom_classe, checkbox) {
	$$('input.' + nom_classe).each (function (element) {
		element.checked = checkbox.checked;
	});
}

// Petit timer pour éviter de charger trop souvent les appels
function lancerFiltre (unique_id) {
	if(timer){
		clearTimeout(timer);
		timer = null;
	}
	// Si on filtre on retourne sur la page 1
	$('numero_page_' + unique_id).value = 1;
	timer = setTimeout('filtre("' + unique_id + '")', 500);
}

// Fonction de filtre - appel AJAX
function filtre(unique_id) {

	$('div_filtre_liste_' + unique_id).innerHTML = '';
	$('div_filtre_liste_' + unique_id).addClass('attente');

	//on rend la main au navigateur pour lui laisser le temps d'afficher l'image d'attente et on lance la recherche
	setTimeout('filtreTimer("' + unique_id + '")', 50);
}

function filtreTimer(unique_id){

	new Request({
		url: "/filtre_liste/liste/lister",
		data: $('formulaire_filtre_' + unique_id),
		evalScripts:true,
		onSuccess: function(retour_ajax) {
			$('div_filtre_liste_' + unique_id).removeClass('attente');
			$('div_filtre_liste_' + unique_id).innerHTML = retour_ajax;
			$('div_filtre_liste_' + unique_id).scrollLeft = $("div_filtre_entete_" + unique_id).scrollLeft;

			currentUniqueId = unique_id;
			memoIndiceCurrentColonne = -1;
			redimensonneColonne(null,0);
			appliquerApresFiltre(unique_id);
		}
	}).send();
}

//A réécrire si on en a besoin
function appliquerApresFiltre(unique_id){
	
}

function changerPage(unique_id) {
	var page_actuelle = numerise($('numero_page_' + unique_id).value);
	var nb_elements_par_ligne = parseInt($('top_' + unique_id).value);
	var nb_elements_total = nb_elements_par_ligne;
	if ($('nb_elements_total_' + unique_id)) {
		nb_elements_total = parseInt($('nb_elements_total_' + unique_id).value);
	}
	var page_max = Math.ceil(nb_elements_total / nb_elements_par_ligne);
	page_max = Math.max(page_max, 1);
	if (isNaN(page_actuelle)) {
		page_actuelle = 1;
	} else if (page_actuelle <= 0) {
		page_actuelle = 1;
	} else if (page_actuelle > page_max) {
		page_actuelle = page_max;
	}

	$('numero_page_' + unique_id).value = page_actuelle;
	
	if(timer){
		clearTimeout(timer);
		timer = null;
	}
	timer = setTimeout('filtre("' + unique_id + '")', 500);
}

function deplaceEnteteFiltre(unique_id){
	$("div_filtre_entete_" + unique_id).scrollLeft = $('div_filtre_liste_' + unique_id).scrollLeft;
}

function cacheImage(evt){

	if(memoDivImage){
		memoDivImage.style.left = "-1000px";
	}

}

function ajouteEventFiltre(unique_id, bouton_autoload){

	var js_onclick = $(bouton_autoload).onclick.toString();
	js_onclick = js_onclick.replace('(event)','()');
	js_onclick = js_onclick.substring(js_onclick.indexOf("{") + 1);
	js_onclick = js_onclick.substring(0,js_onclick.length-2);
	$(bouton_autoload).onclick = function onclick(){
									eval(js_onclick);
									filtre(unique_id);
								};

}

var dbl_click = false;
function autoRedimensionneColonne(event,unique_id){

	//cas de FF
	if(event.target){
		event.srcElement = event.target;
	}

	//on ne gere que le div de redimensionnement
	if(event.srcElement.className != "redimensionne") return;

	//si affichage des colonnes en pourcentage pas de redimensionnement
	if($("unite_" + unique_id).value == "%") return;

	//memo de l'id du filtre
	currentUniqueId = unique_id;

	//colonne déplacée
	if(event.srcElement.id.indexOf("div_redimensionne_entete_colonne_") > -1){
		currentColonne = event.srcElement.id.replace("div_redimensionne_entete_colonne_" + unique_id + "_","");
	}else{
		currentColonne = event.srcElement.id.replace("div_redimensionne_liste_colonne_" + unique_id + "_","");
	}

	var delta = 0;
	memoIndiceCurrentColonne = 0;
	for(var i = 0; i < tableau_info_colonne.length; i++){

		var nom_colonne = tableau_info_colonne[i]["nom"];
		var width = numerise(tableau_info_colonne[i]["width"]);

		if(nom_colonne == currentColonne){
			tableau_info_colonne[i]["width"] = Math.round(numerise($("max_largeur_" + currentUniqueId + "_" + currentColonne).value) * 7.5 + 18);
			delta = tableau_info_colonne[i]["width"] - width;
			memoIndiceCurrentColonne = i;
		}else{
			if(i>memoIndiceCurrentColonne){
				tableau_info_colonne[i]["left"] = tableau_info_colonne[i]["left"] + delta;
			}
		}

	}

	redimensonneColonne(null,0);
	finRedimensionneColonne();

}

function debutRedimensionneColonne(event,unique_id){

	//cas de FF
	if(event.target){
		event.srcElement = event.target;
	}

	//on ne gere que le div de redimensionnement
	if(event.srcElement.className != "redimensionne") return;

	//memo de l'id du filtre
	currentUniqueId = unique_id;

	//memo de la position initiale de la souris
	memoClickX = event.clientX;

	//colonne déplacée
	if(event.srcElement.id.indexOf("div_redimensionne_entete_colonne_") > -1){
		currentColonne = event.srcElement.id.replace("div_redimensionne_entete_colonne_" + unique_id + "_","");
	}else{
		currentColonne = event.srcElement.id.replace("div_redimensionne_liste_colonne_" + unique_id + "_","");
	}
}

function redimensonneColonne(event,p_delta){

	if(event){

		if(currentUniqueId == "") return;
		if(currentColonne == "") return;

		//calcul du décalage par rapport au click initial
		var delta = numerise(event.clientX - memoClickX);
		var max_largeur = $("formulaire_filtre_" + currentUniqueId).offsetWidth;
	}else{
		var delta = p_delta;
	}
	var unite = $("unite_" + currentUniqueId).value;

	//decalage des colonnes
	for(var i = 0; i < tableau_info_colonne.length; i++){

		var nom_colonne = tableau_info_colonne[i]["nom"];
		var left = numerise(tableau_info_colonne[i]["left"]);
		var width = numerise(tableau_info_colonne[i]["width"]);

		if(unite == "%"){

			//recalcul du delta en pourcentage
			delta_pourcent = Math.round(delta / max_largeur * 100);

			//cas colonne en cours, on ne peut pas redimensionner la derniere colonne
			if(i == memoIndiceCurrentColonne && tableau_info_colonne.length - 1 > memoIndiceCurrentColonne){

				var nom_colonne_n1 = tableau_info_colonne[i + 1]["nom"];
				var left_n1 = numerise(tableau_info_colonne[i + 1]["left"]);
				var width_n1 = numerise(tableau_info_colonne[i + 1]["width"]);

				//on limite a 2% min la largeur de la colonne en cours
				if(width + delta_pourcent < 2){
					delta_pourcent = -1 * width + 2;
				}

				//on limite a 2% min la largeur de la colonne n + 1
				if(width_n1 - delta_pourcent < 2){
					delta_pourcent = width_n1 - 2;
				}

				//ajustement de la colonne
				$("div_liste_colonne_" + currentUniqueId +  "_" + nom_colonne).style.width = (width + delta_pourcent) + unite;
				$("div_entete_colonne_" + currentUniqueId +  "_" + nom_colonne).style.width = (width + delta_pourcent) + unite;

				//ajustement de la colonne n + 1
				$("div_liste_colonne_" + currentUniqueId +  "_" + nom_colonne_n1).style.width = (width_n1 - delta_pourcent) + unite;
				$("div_entete_colonne_" + currentUniqueId +  "_" + nom_colonne_n1).style.width = (width_n1 - delta_pourcent) + unite;
				$("div_liste_colonne_" + currentUniqueId +  "_" + nom_colonne_n1).style.left = (left_n1 + delta_pourcent) + unite;
				$("div_entete_colonne_" + currentUniqueId +  "_" + nom_colonne_n1).style.left = (left_n1 + delta_pourcent) + unite;

			}

			//cas particulier pour ajuster les colonnes avec la meme largeur des entetes
			//par exemple utile après une recherche qui recherche la liste avec les largeurs par defaut
			if(i > memoIndiceCurrentColonne){
				if ($("div_liste_colonne_" + currentUniqueId +  "_" + nom_colonne)) {
					$("div_liste_colonne_" + currentUniqueId +  "_" + nom_colonne).style.left = $("div_entete_colonne_" + currentUniqueId +  "_" + nom_colonne).style.left;
					$("div_liste_colonne_" + currentUniqueId +  "_" + nom_colonne).style.width = $("div_entete_colonne_" + currentUniqueId +  "_" + nom_colonne).style.width;
				}

			}

		}else{

			//cas colonne en cours
			if(i == memoIndiceCurrentColonne){

				//on limite a 20px min la largeur de la colonne
				if(width + delta < 20){
					delta = -1 * width + 20;
				}

				$("div_liste_colonne_" + currentUniqueId +  "_" + nom_colonne).style.width = (width + delta) + unite;
				$("div_entete_colonne_" + currentUniqueId +  "_" + nom_colonne).style.width = (width + delta) + unite;

			}

			//cas colonne après
			if(i > memoIndiceCurrentColonne){
				if($("div_liste_colonne_" + currentUniqueId +  "_" + nom_colonne)){

					$("div_liste_colonne_" + currentUniqueId +  "_" + nom_colonne).style.left = (left + delta) + unite;
					$("div_liste_colonne_" + currentUniqueId +  "_" + nom_colonne).style.width = width + unite;
					$("div_entete_colonne_" + currentUniqueId +  "_" + nom_colonne).style.left = (left + delta) + unite;

				}
			}
		}
	}

	deplaceEnteteFiltre(currentUniqueId);

}

function finRedimensionneColonne(){
	currentUniqueId = "";
	currentColonne = "";
}


/**
* Retourne un nombre quelque soit le parametre en essayant de nettoyer la chaine
*/
var regExpNumerise;
function numerise(valeur){

	//si premier appel on construit l'expression reguliere
	if(regExpNumerise == null){
		//construction de la chaine des caracteres supprim?s
		var str_reg = String.fromCharCode(47);
		for(var i=1;i<=43;i++){
			str_reg += "|" + String.fromCharCode(i);
		}
		for(var i=58;i<=255;i++){
			str_reg += "|" + String.fromCharCode(i);
		}
		//gestion des caracteres speciaux de l'expression reguliere
		var temp = "$()*+?[\\]^{|}";
		for(var i=0;i<temp.length;i++){
			str_reg = str_reg.replace("|" + temp.charAt(i) + "|","|\\" + temp.charAt(i) + "|");
		}
		regExpNumerise = new RegExp(str_reg,"g");

		//caracteres conserv?s :
		// 1234567890.,-
	}

	//application de l'expression reguliere
	var temp = (valeur + "").replace(regExpNumerise,"");

	//on remplace la , par un . pour que l'expression soit reconnue numerique par le javascript
	temp = temp.replace(/,/g,".");

	//convertion en numerique
	temp = parseFloat(temp,10);

	//si malgres tout l'expression n'est pas numerique on retourne zero
	if(isNaN(temp) || temp == "NaN") temp = 0;

	return temp;
}

var tableau_old_tri = Array();
function changeTri(unique_id, nom_champ){

	if(tableau_old_tri[unique_id]){

		var tableau_temp = tableau_old_tri[unique_id].split("#");

		//meme champs ?
		if(tableau_temp[0] == nom_champ){

			//oui, changement de sens
			if(tableau_temp[1] == "ASC"){
				$("img_ordre_" + unique_id + "_" + nom_champ).removeClass('tri_asc');
				$("img_ordre_" + unique_id + "_" + nom_champ).addClass('tri_desc');
				tableau_old_tri[unique_id] = nom_champ + "#DESC";
			}else{
				$("img_ordre_" + unique_id + "_" + nom_champ).removeClass('tri_asc');
				$("img_ordre_" + unique_id + "_" + nom_champ).removeClass('tri_desc');
				tableau_old_tri[unique_id] = null;
			}

		}else{

			//non, on reset l'ancien champ
			$("img_ordre_" + unique_id + "_" + nom_champ).removeClass('tri_asc');
			$("img_ordre_" + unique_id + "_" + nom_champ).removeClass('tri_desc');

			//premier tri, ordre croissant
			$("img_ordre_" + unique_id + "_" + nom_champ).removeClass('tri_desc');
			$("img_ordre_" + unique_id + "_" + nom_champ).addClass('tri_asc');
			tableau_old_tri[unique_id] = nom_champ + "#ASC";

		}


	}else{

		//premier tri, ordre croissant
		$("img_ordre_" + unique_id + "_" + nom_champ).removeClass('tri_desc');
		$("img_ordre_" + unique_id + "_" + nom_champ).addClass('tri_asc');
		tableau_old_tri[unique_id] = nom_champ + "#ASC";

	}

	if(tableau_old_tri[unique_id]){
		$("ordre_" + unique_id).value = tableau_old_tri[unique_id].replace("#"," ");
	}else{
		$("ordre_" + unique_id).value = "";
	}

	filtreTimer(unique_id);

}

/*
 * fonction qui gère le fait que toute la ligne se mette en surbrillance, lorsqu'on passe la souris dessus (hover)
 */
function surlignerLigne (unique_id, num_ligne) {
	$$('#formulaire_filtre_' + unique_id + ' div.ligne_' + num_ligne).addClass('ligne_hover');
}
function libererLigne (unique_id, num_ligne) {
	$$('#formulaire_filtre_' + unique_id + ' div.ligne_' + num_ligne).removeClass('ligne_hover');
}


/**
* Export au format xls des filtre/liste
*/
function exportXLS(unique_id){

	var formulaire = document.createElement('form');
	formulaire.style.display = "none";
	$$("body").adopt(formulaire);

	$$("#formulaire_filtre_" + unique_id + " input, #formulaire_filtre_" + unique_id + " select").each(function(el){
		var input = document.createElement('input');
		input.name = el.name;
		input.value = el.value;
		formulaire.adopt(input);
	});

	formulaire.action = '/filtre_liste/liste/lister?xls=1';
	formulaire.target = '_new';
	formulaire.method = 'post';
	formulaire.submit();

	formulaire.destroy();

}

function filtreListePageSuivante(UniqueId){
	
	var numero_page = numerise($("numero_page_" + UniqueId).value);
	numero_page++;
	$("numero_page_" + UniqueId).value = numero_page;
	filtre(UniqueId);
	
}

function filtreListePagePrecedente(UniqueId){
	
	var numero_page = numerise($("numero_page_" + UniqueId).value);
	numero_page--;
	numero_page = Math.max(1,numero_page);
	$("numero_page_" + UniqueId).value = numero_page;
	filtre(UniqueId);
	
}