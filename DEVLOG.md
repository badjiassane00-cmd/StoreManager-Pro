📓 Journal de Développement (DEVLOG)
Nom & Prénom : Assane Badji
Projet : StoreManager Pro (ERP PHP/POO)  



1. Suivi Chronologique des Phases

 [Vendredi - Phase 1] :  Diagrammes Use Case & Classes dans /docs/.
- **Heure de réalisation** : 20H25 
Ce qui a été fait :
Analyse du fonctionnement général de StoreManager Pro.
Réalisation du diagramme de cas d'utilisation.
Réalisation du diagramme de classes.
Identification des principales entités du projet : Utilisateur, Produit, Client, Fournisseur, Commande, Dette et BonLivraison.
Identification des relations entre les différentes entités.
Difficultés / Obstacles :
J'ai eu des difficultés à comprendre les relations entre certaines classes.
J'ai dû revoir la différence entre une classe, un attribut et une relation.
J'ai vérifié que le diagramme de classes correspond bien aux fonctionnalités demandées.


 [Vendredi - Phase 1.2] :  Schéma SQL PostgreSQL / SQLite
- **Heure de réalisation** : 20H30 - 22H30 

Ce qui a été fait :
J’ai ajouté paiements_dettes et paiements_fournisseurs que j'avais hormis lors de mon diagramme de classe
Transformation du diagramme de classes en schéma relationnel.
Identification des tables nécessaires à la base de données.
Création des tables utilisateurs, produits, clients, fournisseurs, commandes, lignes_commandes, dettes, bons_livraison et lignes_bon_livraison.
Ajout des clés primaires.
Ajout des clés étrangères pour relier les tables.
Ajout des contraintes CHECK pour contrôler certaines valeurs.
Création du script PostgreSQL schema.sql.
Création du script SQLite schema_sqlite.sql.
Vérification de la cohérence des relations entre les tables.
Difficultés / Obstacles :
J'ai eu des difficultés à passer du PostgreSQL à SQLite.
J'ai d'abord créé des tables supplémentaires pour les paiements, puis j'ai constaté qu'elles ne correspondaient pas directement au diagramme de classes.
J'ai donc repris le schéma afin de rester fidèle au modèle UML.
J'ai également dû comprendre le rôle des clés étrangères et des contraintes CHECK.

 [Vendredi - Phase 1.3] :  Database Singleton & Fallback (Step 1.3)
- **Heure de réalisation** :22h00 - 23h00

Ce qui a été fait :
Création de la classe Database en tant que Singleton : elle garantit qu'une seule et unique instance de la classe (et donc une seule connexion PDO) existe pendant toute l'exécution du programme, au lieu d'ouvrir une nouvelle connexion à chaque fois qu'on a besoin de parler à la base de données.
Le constructeur __construct() est déclaré private. Concrètement, ça veut dire qu'il est interdit d'écrire new Database() n'importe où ailleurs dans le code (PHP refuserait avec une erreur). La seule façon d'obtenir l'objet est de passer par la méthode statique getInstance().
Deux propriétés private static ont été déclarées : $instance (qui contiendra l'unique objet Database) et $pdo (qui contiendra l'unique connexion PDO). Le mot-clé static signifie que ces propriétés appartiennent à la classe elle-même, pas à un objet en particulier : leur valeur est donc partagée par tout le programme, peu importe combien de fois on appelle Database::getInstance().
La méthode statique getInstance() fait un simple test : si self::$instance vaut encore null (première fois qu'on l'appelle), elle crée l'unique objet avec new self(). Sinon, elle renvoie directement l'objet déjà créé. Résultat : new self() n'est exécuté qu'une seule fois dans toute l'application.
La méthode connexion() fait le vrai travail de connexion :
Elle vérifie d'abord si self::$pdo est déjà rempli — si oui, elle le retourne tel quel (pas de reconnexion inutile).
Sinon, elle tente new PDO("pgsql:host=localhost;dbname=storemanager;port=5432", "postgres", "narutobadji") dans un bloc try, pour se connecter à PostgreSQL.
Si cette connexion réussit, deux attributs sont configurés sur l'objet PDO : PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC (pour que chaque ligne récupérée en base soit renvoyée sous forme de tableau associatif ['colonne' => valeur], plus lisible qu'un tableau numérique) et PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION (pour que toute erreur SQL déclenche une exception PHP au lieu d'échouer silencieusement).
Si la connexion à PostgreSQL échoue (serveur PostgreSQL non démarré, mauvais identifiants, etc.), PHP lève automatiquement une PDOException. Le bloc catch (PDOException $ex) intercepte cette erreur et bascule sur un plan B : une connexion SQLite locale, vers un fichier erp.db situé à la racine du projet (chemin construit avec dirname(__DIR__, 2), qui remonte de deux niveaux de dossiers depuis src/Core/ pour arriver à la racine). Les mêmes attributs FETCH_ASSOC et ERRMODE_EXCEPTION sont réappliqués sur cette connexion SQLite, pour que le reste de l'application se comporte exactement pareil, qu'elle parle à PostgreSQL ou à SQLite.
Une méthode deconnecte() a été ajoutée pour remettre self::$pdo à null (utile surtout pour les tests, afin de forcer une reconnexion propre).
Quatre méthodes utilitaires ont été construites au-dessus de connexion(), pour ne jamais avoir à écrire du code PDO brut ailleurs dans l'application :
query(string $sql, bool $single = true) : exécute une requête SQL sans paramètre (ex: SELECT * FROM produits) et renvoie soit une seule ligne (fetch()), soit toutes les lignes (fetchAll()) selon $single.
prepare(string $sql, array $datas) : prépare une requête paramétrée (? ou :nom) avec prepare(), puis l'exécute immédiatement avec execute($datas). C'est la méthode qui protège contre les injections SQL, car les valeurs ne sont jamais concaténées directement dans le texte de la requête.
executeQuery(string $sql, array $datas, bool $single = true) : combine prepare() puis récupère le résultat (une ligne ou toutes les lignes), pour les SELECT avec paramètres.
executeUpdate(string $sql, array $datas) : pour les INSERT / UPDATE / DELETE. Elle a un comportement particulier : si la requête commence par INSERT (testé avec str_starts_with(strtoupper(trim($sql)), 'INSERT')), elle renvoie l'id généré automatiquement par la base via lastInsertId(). Sinon (UPDATE/DELETE), elle renvoie le nombre de lignes affectées via rowCount().
getAllTables(string $nameTable) : raccourci pour faire un SELECT * FROM $nameTable complet.
Difficultés / Obstacles (ayant commencé la POO seulement hier) :
Le concept le plus difficile a été de comprendre la différence entre une propriété/méthode normale ($this->...) et une propriété/méthode static (self::...). Il a fallu plusieurs essais pour bien saisir que static veut dire "appartient à la classe, pas à un objet précis", et que c'est justement ce qui permet au Singleton de fonctionner : sans static, chaque new Database() aurait sa propre copie de $pdo, et le principe d'unicité n'existerait plus.
Comprendre pourquoi le constructeur doit être private a demandé une explication supplémentaire : au début, ça semblait juste être une règle arbitraire, avant de comprendre que c'est ce qui empêche concrètement n'importe quel autre fichier du projet de faire new Database() et de créer une deuxième connexion "en douce".
Le mécanisme try { ... } catch (PDOException $ex) { ... } était totalement nouveau : il a fallu comprendre qu'une exception est un objet d'erreur que PHP "lance" (throw, ici fait automatiquement par PDO) quand quelque chose échoue, et que le code placé dans catch ne s'exécute que si une erreur a réellement été levée dans le try. Sans ce mécanisme, une erreur de connexion PostgreSQL aurait simplement fait planter tout le script avec une page blanche.

