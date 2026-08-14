

PRAGMA foreign_keys = ON;

CREATE TABLE roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL UNIQUE,
    CHECK (nom IN ('ADMIN', 'VENTE', 'STOCK', 'INVENTAIRE'))
);

CREATE TABLE utilisateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    mot_de_passe TEXT NOT NULL,
    role_id INTEGER NOT NULL,
    actif INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE produits (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL UNIQUE,
    prix_unitaire NUMERIC NOT NULL,
    quantite_stock INTEGER NOT NULL DEFAULT 0,
    seuil_alerte INTEGER NOT NULL DEFAULT 5,
    CHECK (prix_unitaire >= 0),
    CHECK (quantite_stock >= 0),
    CHECK (seuil_alerte >= 0)
);

CREATE TABLE clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    telephone TEXT,
    email TEXT,
    limite_credit NUMERIC NOT NULL DEFAULT 0,
    CHECK (limite_credit >= 0)
);

CREATE TABLE fournisseurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL UNIQUE,
    telephone TEXT,
    adresse TEXT,
    email TEXT
);

CREATE TABLE commandes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date_vente TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    client_id INTEGER,
    utilisateur_id INTEGER NOT NULL,
    montant_total NUMERIC NOT NULL DEFAULT 0,
    montant_verse NUMERIC NOT NULL DEFAULT 0,
    statut_paiement TEXT NOT NULL DEFAULT 'COMPTANT',
    CHECK (statut_paiement IN ('COMPTANT', 'CREDIT')),
    CHECK (montant_total >= 0),
    CHECK (montant_verse >= 0),
    CHECK (montant_verse <= montant_total),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE lignes_commandes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    commande_id INTEGER NOT NULL,
    produit_id INTEGER NOT NULL,
    quantite INTEGER NOT NULL,
    prix_unitaire NUMERIC NOT NULL,
    CHECK (quantite > 0),
    CHECK (prix_unitaire >= 0),
    UNIQUE (commande_id, produit_id),
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);

CREATE TABLE dettes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    commande_id INTEGER NOT NULL UNIQUE,
    client_id INTEGER NOT NULL,
    montant_initial NUMERIC NOT NULL,
    montant_paye NUMERIC NOT NULL DEFAULT 0,
    statut TEXT NOT NULL DEFAULT 'NON_SOLDEE',
    CHECK (montant_initial > 0),
    CHECK (montant_paye >= 0),
    CHECK (montant_paye <= montant_initial),
    CHECK (statut IN ('NON_SOLDEE', 'PARTIELLE', 'SOLDEE')),
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

CREATE TABLE paiements_dettes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    dette_id INTEGER NOT NULL,
    montant NUMERIC NOT NULL,
    date_paiement TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mode_paiement TEXT NOT NULL DEFAULT 'ESPECES',
    CHECK (montant > 0),
    CHECK (mode_paiement IN ('ESPECES', 'WAVE', 'OM', 'CARTE', 'AUTRE')),
    FOREIGN KEY (dette_id) REFERENCES dettes(id) ON DELETE CASCADE
);

CREATE TABLE bons_livraison (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fournisseur_id INTEGER NOT NULL,
    utilisateur_id INTEGER NOT NULL,
    date_reception TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    montant_facture NUMERIC NOT NULL DEFAULT 0,
    statut_reglement TEXT NOT NULL DEFAULT 'IMPAYEE',
    CHECK (montant_facture >= 0),
    CHECK (statut_reglement IN ('PAYEE', 'IMPAYEE')),
    FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE lignes_bons_livraison (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bon_livraison_id INTEGER NOT NULL,
    produit_id INTEGER NOT NULL,
    quantite_livree INTEGER NOT NULL DEFAULT 0,
    cout_achat NUMERIC NOT NULL,
    CHECK (quantite_livree >= 0),
    CHECK (cout_achat >= 0),
    UNIQUE (bon_livraison_id, produit_id),
    FOREIGN KEY (bon_livraison_id) REFERENCES bons_livraison(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);

CREATE TABLE paiements_fournisseurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fournisseur_id INTEGER NOT NULL,
    bon_livraison_id INTEGER,
    montant NUMERIC NOT NULL,
    date_paiement TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mode_paiement TEXT NOT NULL DEFAULT 'ESPECES',
    CHECK (montant > 0),
    CHECK (mode_paiement IN ('ESPECES', 'WAVE', 'OM', 'CARTE', 'AUTRE')),
    FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id),
    FOREIGN KEY (bon_livraison_id) REFERENCES bons_livraison(id) ON DELETE SET NULL
);

CREATE INDEX idx_commandes_date_vente ON commandes(date_vente);
CREATE INDEX idx_commandes_client ON commandes(client_id);
CREATE INDEX idx_lignes_commandes_commande ON lignes_commandes(commande_id);
CREATE INDEX idx_dettes_client ON dettes(client_id);
CREATE INDEX idx_paiements_dettes_dette ON paiements_dettes(dette_id);
CREATE INDEX idx_bl_fournisseur ON bons_livraison(fournisseur_id);
CREATE INDEX idx_lignes_bl_bon ON lignes_bons_livraison(bon_livraison_id);
CREATE INDEX idx_paiements_fournisseurs_fournisseur ON paiements_fournisseurs(fournisseur_id);

INSERT INTO roles (nom) VALUES
    ('ADMIN'),
    ('VENTE'),
    ('STOCK'),
    ('INVENTAIRE');

INSERT INTO utilisateurs (nom, email, mot_de_passe, role_id) VALUES
    ('Admin Boutique', 'admin@storemanager.sn', 'demo1234', (SELECT id FROM roles WHERE nom = 'ADMIN')),
    ('Chargé de Vente', 'vente@storemanager.sn', 'demo1234', (SELECT id FROM roles WHERE nom = 'VENTE')),
    ('Chargé de Stock', 'stock@storemanager.sn', 'demo1234', (SELECT id FROM roles WHERE nom = 'STOCK')),
    ('Inventaire', 'inventaire@storemanager.sn', 'demo1234', (SELECT id FROM roles WHERE nom = 'INVENTAIRE'));

INSERT INTO produits (nom, prix_unitaire, quantite_stock, seuil_alerte) VALUES
    ('Bidon d''huile 5L', 6000, 5, 5),
    ('Carton de lait', 18000, 12, 5),
    ('Carton de savon', 8500, 3, 5),
    ('Huile de palme 1L', 1500, 0, 5),
    ('Paquet de sucre 1kg', 750, 20, 5),
    ('Sac de riz 50kg', 30000, 8, 3);

INSERT INTO clients (nom, telephone, email, limite_credit) VALUES
    ('Maimouna Diallo', '701122334', NULL, 150000),
    ('Moussa Sarr', '769876543', NULL, 200000),
    ('Fama Diouf', '781234567', NULL, 100000),
    ('Abdou Ndiaye', '770000000', NULL, 100000);

INSERT INTO fournisseurs (nom, telephone, adresse, email) VALUES
    ('Comptoir Céréalier Sénégalais', NULL, NULL, NULL),
    ('Grossiste Diop & Frères', NULL, NULL, NULL),
    ('Sénégal Import-Export', NULL, NULL, NULL);
