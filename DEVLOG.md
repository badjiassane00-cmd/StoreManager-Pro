📓 Journal de Développement (DEVLOG) Nom & Prénom : Assane Badji Projet : StoreManager Pro (ERP PHP/POO)

Suivi Chronologique des Phases
[Vendredi - Phase 1] : Diagrammes Use Case & Classes dans /docs/.

Heure de réalisation : 20H25 Ce qui a été fait : Analyse du fonctionnement général de StoreManager Pro. Réalisation du diagramme de cas d'utilisation. Réalisation du diagramme de classes. Identification des principales entités du projet : Utilisateur, Produit, Client, Fournisseur, Commande, Dette et BonLivraison. Identification des relations entre les différentes entités. Difficultés / Obstacles : J'ai eu des difficultés à comprendre les relations entre certaines classes. J'ai dû revoir la différence entre une classe, un attribut et une relation. J'ai vérifié que le diagramme de classes correspond bien aux fonctionnalités demandées.
[Vendredi - Phase 1.2] : Schéma SQL PostgreSQL / SQLite

Heure de réalisation : 20H30 - 22H30
Ce qui a été fait : J’ai ajouté paiements_dettes et paiements_fournisseurs que j'avais hormis lors de mon diagramme de classe Transformation du diagramme de classes en schéma relationnel. Identification des tables nécessaires à la base de données. Création des tables utilisateurs, produits, clients, fournisseurs, commandes, lignes_commandes, dettes, bons_livraison et lignes_bon_livraison. Ajout des clés primaires. Ajout des clés étrangères pour relier les tables. Ajout des contraintes CHECK pour contrôler certaines valeurs. Création du script PostgreSQL schema.sql. Création du script SQLite schema_sqlite.sql. Vérification de la cohérence des relations entre les tables. Difficultés / Obstacles : J'ai eu des difficultés à passer du PostgreSQL à SQLite. J'ai d'abord créé des tables supplémentaires pour les paiements, puis j'ai constaté qu'elles ne correspondaient pas directement au diagramme de classes. J'ai donc repris le schéma afin de rester fidèle au modèle UML. J'ai également dû comprendre le rôle des clés étrangères et des contraintes CHECK.

[Vendredi - Phase 1.3] : Database Singleton & Fallback (Step 1.3)

Heure de réalisation :22h00 - 23h00
Ce qui a été fait : Création de la classe Database en tant que Singleton : elle garantit qu'une seule et unique instance de la classe (et donc une seule connexion PDO) existe pendant toute l'exécution du programme, au lieu d'ouvrir une nouvelle connexion à chaque fois qu'on a besoin de parler à la base de données. Le constructeur __construct() est déclaré private. Concrètement, ça veut dire qu'il est interdit d'écrire new Database() n'importe où ailleurs dans le code (PHP refuserait avec une erreur). La seule façon d'obtenir l'objet est de passer par la méthode statique getInstance(). Deux propriétés private static ont été déclarées : $instance (qui contiendra l'unique objet Database) et $pdo (qui contiendra l'unique connexion PDO). Le mot-clé static signifie que ces propriétés appartiennent à la classe elle-même, pas à un objet en particulier : leur valeur est donc partagée par tout le programme, peu importe combien de fois on appelle Database::getInstance().

Difficultés / Obstacles :
Ayant commencé à apprendre la POO seulement hier, la notion de Singleton n'était pas évidente au début : comprendre pourquoi le constructeur doit être private (pour empêcher new Database() de l'extérieur) et pourquoi on utilise des propriétés static (pour qu'il n'existe qu'une seule instance et une seule connexion PDO partagée dans toute l'application) a demandé plusieurs relectures.
Le mécanisme try/catch pour le fallback PostgreSQL → SQLite était aussi nouveau : il a fallu comprendre qu'une PDOException est levée automatiquement par PHP quand la connexion échoue, et que le catch permet de "rattraper" cette erreur pour exécuter un plan B au lieu de faire planter le script.
Petite hésitation sur le diagramme de classes concernant les associations entre Commande et Dette (relation 0..1, une commande ne génère une dette que si elle est à crédit).



###  [Samedi - Phase 2.1] : POO, Repositories & Ventes POS

 **Heure de réalisation** : 09h00 - 11h00
 **Ce qui a été fait** :Entités POO Pure
  J'ai réécrit mes 12 classess de description  pour
  qu'elles respectent l'encapsulation : propriétés en `private`,
 **Difficultés / Obstacles** :
  Comprendre pourquoi mes Repository plantaient : les requires manquant , le chemin utilisé par le fallback.
 savoir quelle methodes metiers écrit car je m'étais habitué à écrie les classes de descriptions  sans methode metiers.

###  [Samedi - Phase 2.1] : Repositories & SQL Sécurisé

  **Heure de réalisation** : 11h00 - 13h00
 **Ce qui a été fait** :Repositories & SQL Sécurisé 
 Ce qui a été fait : Création des entités PHP (Produit, Client, Commande, Dette, Fournisseur, BonLivraison, etc.). Ce sont des  classes avec des propriétés typées Création des Repositories (ProduitRepository, ClientRepository, FournisseurRepository) qui contiennent toutes les requêtes SQL, avec des requêtes préparées PDO (paramètres nommés comme :nom, :id) pour se protéger des injections SQL. Chaque Repository a une méthode privée qui transforme une ligne SQL (tableau associatif) en objet PHP correspondant.
**Difficultés / Obstacles** :
la première c'était comment convertir les données venant de la base de données pour pouvoir esperer les utiliser .
 Autre difficulté : ILIKE (recherche insensible à la casse) ne marche qu'avec PostgreSQL, pas avec SQLite. J'ai dû utiliser LOWER(...) LIKE LOWER(...) à la place, pour que la recherche fonctionne pareil sur les deux bases à cause du fallback automatique.



 ###  [Samedi - Phase 2.3] : Service Métier Vente POS & Transaction SQL

 **Heure de réalisation** : 14h00 - 17h00
 **Ce qui a été fait** :VenteService avec transaction SQL
 J'ai créé CommandeModel et DetteModel qui manquaient pour pouvoir écrire VenteService. VenteService construit une Commande à partir du panier envoyé par le formulaire (produit_id => quantité), vérifie le stock disponible et la limite de crédit du client AVANT d'ouvrir la transaction, puis fait beginTransaction / insertion de la commande + des lignes + décrémentation du stock + création de la dette si vente à crédit / commit. Si une étape échoue en cours de route (stock insuffisant détecté trop tard, erreur SQL), rollBack pour que rien ne soit enregistré à moitié.
 **Difficultés / Obstacles** :
 La difficulté c'était de comprendre pourquoi il fallait vérifier le stock  ET la limite de crédit AVANT de commencer la transaction plutôt que pendant : si on vérifie après beginTransaction, on doit quand même faire un rollBack propre, donc autant filtrer les cas évidents avant pour ne pas ouvrir une transaction pour rien. Autre chose pas évidente : pourquoi je dois d'abord créer la Commande en base (pour avoir son id généré par SERIAL) avant de  pouvoir créer la Dette,puisque dettes.commande_id est une clé étrangère obligatoire. Ça oblige à faire les opérations dans un ordre précis à l'intérieur de la transaction.



###  [Samedi - Phase 2.4] : POSController.php et vue views/pos/index.php.


 **Heure de réalisation** : 17h00 - 20h00
 **Ce qui a été fait** :POSController et vue caisse
 J'ai ajouté Router.php et SessionManager pour avoir un vrai point d'entrée public/index.php avec des routes.
 J'ai fait le panier pour qu'il vive entièrement dans la session PHP ($_SESSION["pos_panier"]), avec une route POST dédiée pour chaque action.
 **Difficultés / Obstacles** :
 Délimiter les limites de la view.
Rediriger après chaque traitement POST pour éviter qu'un rafraîchissementvde page renvoie deux fois le même formulaire, et faire transiter les messages de succès/erreur par la session puisqu'ils ne survivent pas à une redirection sinon.
