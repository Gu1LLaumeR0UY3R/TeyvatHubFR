/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id_admin` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pseudo_admin` varchar(100) NOT NULL,
  `email_admin` varchar(255) NOT NULL,
  `mot_de_passe_admin` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'moderateur',
  `legacy_permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `photo_profil` varchar(255) DEFAULT NULL,
  `banniere_admin` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `admin_pseudo_admin_unique` (`pseudo_admin`),
  UNIQUE KEY `admin_email_admin_unique` (`email_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `amitie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amitie` (
  `id_amitie` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid_demandeur` bigint(20) unsigned NOT NULL,
  `fid_receveur` bigint(20) unsigned NOT NULL,
  `statut` varchar(20) NOT NULL DEFAULT 'en_attente',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_amitie`),
  KEY `fk_ami_demandeur` (`fid_demandeur`),
  KEY `fk_ami_receveur` (`fid_receveur`),
  CONSTRAINT `fk_ami_demandeur` FOREIGN KEY (`fid_demandeur`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ami_receveur` FOREIGN KEY (`fid_receveur`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `animal_ingredient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `animal_ingredient` (
  `fid_animal` int(10) unsigned NOT NULL,
  `fid_ingredient` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fid_animal`,`fid_ingredient`),
  KEY `fk_ai_ingre` (`fid_ingredient`),
  CONSTRAINT `fk_ai_animal` FOREIGN KEY (`fid_animal`) REFERENCES `animaux` (`id_animal`) ON DELETE CASCADE,
  CONSTRAINT `fk_ai_ingre` FOREIGN KEY (`fid_ingredient`) REFERENCES `ingrédient` (`id_ingredient`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `animal_region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `animal_region` (
  `fid_animal` int(10) unsigned NOT NULL,
  `fid_region` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fid_animal`,`fid_region`),
  KEY `fk_ar_nation` (`fid_region`),
  CONSTRAINT `fk_ar_animal` FOREIGN KEY (`fid_animal`) REFERENCES `animaux` (`id_animal`) ON DELETE CASCADE,
  CONSTRAINT `fk_ar_nation` FOREIGN KEY (`fid_region`) REFERENCES `nation` (`id_region`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `animaux`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `animaux` (
  `id_animal` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_animal` varchar(150) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descri_animal` text DEFAULT NULL,
  `fid_TAnimal` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_animal`),
  UNIQUE KEY `animaux_slug_unique` (`slug`),
  KEY `fk_ani_type` (`fid_TAnimal`),
  CONSTRAINT `fk_ani_type` FOREIGN KEY (`fid_TAnimal`) REFERENCES `type_animal` (`id_TAnimal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `aptitude`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aptitude` (
  `id_aptitude` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titre_apti` varchar(200) NOT NULL,
  `descri_apti` text DEFAULT NULL,
  `lvl_apt` tinyint(4) NOT NULL DEFAULT 1,
  `sub_Apt` text DEFAULT NULL,
  `fid_TypeApti` int(10) unsigned NOT NULL,
  `fid_perso` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_aptitude`),
  KEY `fk_apti_type` (`fid_TypeApti`),
  KEY `fk_apti_perso` (`fid_perso`),
  CONSTRAINT `fk_apti_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE,
  CONSTRAINT `fk_apti_type` FOREIGN KEY (`fid_TypeApti`) REFERENCES `type_apti` (`id_TypeApti`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `arm_stats_niveau`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arm_stats_niveau` (
  `id_ASN` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lvl_ASN` tinyint(4) NOT NULL,
  `main_stat` double DEFAULT NULL,
  `subs_stats` double DEFAULT NULL,
  `fid_arme` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_ASN`),
  UNIQUE KEY `uk_asn_arme` (`lvl_ASN`,`fid_arme`),
  KEY `fk_asn_arme` (`fid_arme`),
  CONSTRAINT `fk_asn_arme` FOREIGN KEY (`fid_arme`) REFERENCES `armes` (`id_arme`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `arm_stats_rang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arm_stats_rang` (
  `id_ASR` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rang_ASR` tinyint(4) NOT NULL,
  `descri_ASR` text DEFAULT NULL,
  `fid_arme` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_ASR`),
  UNIQUE KEY `uk_asr_arme` (`rang_ASR`,`fid_arme`),
  KEY `fk_asr_arme` (`fid_arme`),
  CONSTRAINT `fk_asr_arme` FOREIGN KEY (`fid_arme`) REFERENCES `armes` (`id_arme`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `armes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `armes` (
  `id_arme` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_arme` varchar(150) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descr_arme` text DEFAULT NULL,
  `nom_competence` varchar(200) DEFAULT NULL,
  `main_stat_type` varchar(100) DEFAULT NULL,
  `sub_stat_type` varchar(100) DEFAULT NULL,
  `fid_TArmes` int(10) unsigned NOT NULL,
  `fid_etoile` int(10) unsigned NOT NULL,
  `fid_provenance` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_arme`),
  UNIQUE KEY `armes_slug_unique` (`slug`),
  KEY `fk_arme_type` (`fid_TArmes`),
  KEY `fk_arme_etoile` (`fid_etoile`),
  KEY `fk_arme_provenance_idx` (`fid_provenance`),
  CONSTRAINT `fk_arme_etoile` FOREIGN KEY (`fid_etoile`) REFERENCES `etoile` (`id_etoile`),
  CONSTRAINT `fk_arme_provenance` FOREIGN KEY (`fid_provenance`) REFERENCES `provenance` (`id_provenance`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_arme_type` FOREIGN KEY (`fid_TArmes`) REFERENCES `type_armes` (`id_TArmes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `artefact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `artefact` (
  `id_artefact` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_artefact` varchar(150) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `bonus_2p` text DEFAULT NULL,
  `bonus_4p` text DEFAULT NULL,
  `fid_rareté` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_artefact`),
  UNIQUE KEY `artefact_slug_unique` (`slug`),
  KEY `artefact_fid_rareté_foreign` (`fid_rareté`),
  CONSTRAINT `artefact_fid_rareté_foreign` FOREIGN KEY (`fid_rareté`) REFERENCES `rareté` (`id_rareté`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `id_article` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `extrait` text DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `cover_image` varchar(500) DEFAULT NULL,
  `statut` enum('brouillon','publié') NOT NULL DEFAULT 'brouillon',
  `published_at` timestamp NULL DEFAULT NULL,
  `fid_admin` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_article`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biens` (
  `id_biens` int(10) unsigned NOT NULL,
  `designation_bien` varchar(255) DEFAULT NULL,
  `rue_biens` varchar(255) NOT NULL,
  `complement_biens` varchar(255) DEFAULT NULL,
  `superficie_biens` decimal(10,2) NOT NULL,
  `description_biens` text DEFAULT NULL,
  `animaux_biens` tinyint(1) DEFAULT 0,
  `nb_couchage` int(11) NOT NULL,
  `id_TypeBien` int(11) NOT NULL,
  `id_commune` mediumint(8) unsigned NOT NULL,
  `id_locataire` int(10) unsigned DEFAULT NULL,
  `statut_validation` enum('en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente',
  `date_soumission` datetime DEFAULT current_timestamp(),
  `date_validation` datetime DEFAULT NULL,
  `id_admin_validateur` int(11) DEFAULT NULL,
  `motif_refus` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bio` (
  `id_bio` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titre_bio` varchar(200) DEFAULT NULL,
  `descri_bio` text DEFAULT NULL,
  `fid_perso` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_bio`),
  UNIQUE KEY `uk_bio_perso` (`fid_perso`),
  CONSTRAINT `fk_bio_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `blocages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocages` (
  `id_blocage` int(10) unsigned NOT NULL,
  `id_biens` int(10) unsigned NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `motif` enum('personnel','entretien','fermeture','autre') DEFAULT 'personnel',
  `commentaire` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `blog_article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_article` (
  `id_article` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titre_article` varchar(180) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `extrait` text DEFAULT NULL,
  `layout_json` longtext DEFAULT NULL,
  `statut` varchar(20) NOT NULL DEFAULT 'brouillon',
  `date_publication` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_article`),
  UNIQUE KEY `uk_blog_article_slug` (`slug`),
  KEY `idx_blog_article_statut_publication` (`statut`,`date_publication`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `blog_slug`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_slug` (
  `id_blog_slug` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug_base` varchar(120) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_blog_slug`),
  UNIQUE KEY `blog_slug_slug_base_unique` (`slug_base`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cadres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cadres` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chronologie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chronologie` (
  `id_chrono` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `resume` text DEFAULT NULL,
  `periode` varchar(100) DEFAULT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `fid_region` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_chrono`),
  KEY `fk_chrono_nation` (`fid_region`),
  CONSTRAINT `fk_chrono_nation` FOREIGN KEY (`fid_region`) REFERENCES `nation` (`id_region`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commentaire_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commentaire_likes` (
  `id_like` int(10) unsigned NOT NULL,
  `id_commentaire` int(10) unsigned NOT NULL,
  `id_locataire` int(10) unsigned NOT NULL,
  `date_like` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commentaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commentaires` (
  `id_commentaire` int(10) unsigned NOT NULL,
  `id_biens` int(10) unsigned NOT NULL,
  `id_locataire` int(10) unsigned NOT NULL,
  `note` tinyint(3) unsigned DEFAULT NULL COMMENT 'Note de 1 à 5 étoiles',
  `titre` varchar(255) DEFAULT NULL,
  `contenu` text NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `statut` enum('publie','en_attente','rejete') NOT NULL DEFAULT 'publie',
  `signale` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commune`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commune` (
  `id_commune` mediumint(8) unsigned NOT NULL,
  `ville_departement` varchar(3) DEFAULT NULL,
  `ville_slug` varchar(255) DEFAULT NULL,
  `ville_nom` varchar(45) DEFAULT NULL,
  `ville_nom_simple` varchar(45) DEFAULT NULL,
  `ville_nom_reel` varchar(45) DEFAULT NULL,
  `ville_nom_soundex` varchar(20) DEFAULT NULL,
  `ville_nom_metaphone` varchar(22) DEFAULT NULL,
  `ville_code_postal` varchar(255) DEFAULT NULL,
  `ville_commune` varchar(3) DEFAULT NULL,
  `ville_code_commune` varchar(5) NOT NULL,
  `ville_arrondissement` smallint(5) unsigned DEFAULT NULL,
  `ville_canton` varchar(4) DEFAULT NULL,
  `ville_amdi` smallint(5) unsigned DEFAULT NULL,
  `ville_population_2010` mediumint(8) unsigned DEFAULT NULL,
  `ville_population_1999` mediumint(8) unsigned DEFAULT NULL,
  `ville_population_2012` mediumint(8) unsigned DEFAULT NULL,
  `ville_densite_2010` int(11) DEFAULT NULL,
  `ville_surface` float DEFAULT NULL,
  `ville_longitude_deg` float DEFAULT NULL,
  `ville_latitude_deg` float DEFAULT NULL,
  `ville_longitude_grd` varchar(9) DEFAULT NULL,
  `ville_latitude_grd` varchar(8) DEFAULT NULL,
  `ville_longitude_dms` varchar(9) DEFAULT NULL,
  `ville_latitude_dms` varchar(8) DEFAULT NULL,
  `ville_zmin` mediumint(9) DEFAULT NULL,
  `ville_zmax` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `constellation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `constellation` (
  `id_const` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titre_const` varchar(200) NOT NULL,
  `descri_const` text DEFAULT NULL,
  `fid_perso` int(10) unsigned NOT NULL,
  `positions_const` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Coordonnées % des 6 points : {"1":{"x":42.5,"y":18.3},...}' CHECK (json_valid(`positions_const`)),
  PRIMARY KEY (`id_const`),
  KEY `fk_const_perso` (`fid_perso`),
  CONSTRAINT `fk_const_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `elements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `elements` (
  `id_element` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_element` varchar(30) NOT NULL,
  PRIMARY KEY (`id_element`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ennemi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ennemi` (
  `id_ennemi` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_ennemi` varchar(150) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descri_enn` text DEFAULT NULL,
  `fid_typeEnne` int(10) unsigned NOT NULL,
  `fid_element` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_ennemi`),
  UNIQUE KEY `ennemi_slug_unique` (`slug`),
  KEY `fk_enn_type` (`fid_typeEnne`),
  KEY `fk_enn_element` (`fid_element`),
  CONSTRAINT `fk_enn_element` FOREIGN KEY (`fid_element`) REFERENCES `elements` (`id_element`),
  CONSTRAINT `fk_enn_type` FOREIGN KEY (`fid_typeEnne`) REFERENCES `type_ennemi` (`id_typeEnnemi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ennemi_region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ennemi_region` (
  `fid_ennemi` int(10) unsigned NOT NULL,
  `fid_region` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fid_ennemi`,`fid_region`),
  KEY `fk_er_nation` (`fid_region`),
  CONSTRAINT `fk_er_ennemi` FOREIGN KEY (`fid_ennemi`) REFERENCES `ennemi` (`id_ennemi`) ON DELETE CASCADE,
  CONSTRAINT `fk_er_nation` FOREIGN KEY (`fid_region`) REFERENCES `nation` (`id_region`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `etoile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `etoile` (
  `id_etoile` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(20) NOT NULL,
  PRIMARY KEY (`id_etoile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evenement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evenement` (
  `id_evenement` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) NOT NULL,
  `descri_courte` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_evenement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `histoire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `histoire` (
  `id_histoire` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid_perso` int(10) unsigned NOT NULL,
  `titre_histoire` varchar(200) DEFAULT NULL,
  `histoire` text NOT NULL,
  `ordre` tinyint(3) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_histoire`),
  KEY `fk_histoire_perso` (`fid_perso`),
  CONSTRAINT `fk_histoire_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ingrédient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingrédient` (
  `id_ingredient` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_ingre` varchar(150) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id_ingredient`),
  UNIQUE KEY `ingrédient_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_at_available_at_index` (`queue`,`reserved_at`,`available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `joueur_arme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `joueur_arme` (
  `fid_joueur` bigint(20) unsigned NOT NULL,
  `fid_arme` int(10) unsigned NOT NULL,
  `niveau` tinyint(4) NOT NULL DEFAULT 1,
  `rang` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`fid_joueur`,`fid_arme`),
  KEY `fk_ja_arme` (`fid_arme`),
  CONSTRAINT `fk_ja_arme` FOREIGN KEY (`fid_arme`) REFERENCES `armes` (`id_arme`) ON DELETE CASCADE,
  CONSTRAINT `fk_ja_joueur` FOREIGN KEY (`fid_joueur`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `joueur_constellation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `joueur_constellation` (
  `fid_joueur` bigint(20) unsigned NOT NULL,
  `fid_perso` int(10) unsigned NOT NULL,
  `fid_constellation` int(10) unsigned NOT NULL,
  `debloquee` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`fid_joueur`,`fid_perso`,`fid_constellation`),
  KEY `fk_jc_perso` (`fid_perso`),
  KEY `fk_jc_const` (`fid_constellation`),
  CONSTRAINT `fk_jc_const` FOREIGN KEY (`fid_constellation`) REFERENCES `constellation` (`id_const`) ON DELETE CASCADE,
  CONSTRAINT `fk_jc_joueur` FOREIGN KEY (`fid_joueur`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jc_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `joueur_personnage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `joueur_personnage` (
  `fid_joueur` bigint(20) unsigned NOT NULL,
  `fid_perso` int(10) unsigned NOT NULL,
  `niveau` tinyint(4) NOT NULL DEFAULT 1,
  `affinite` tinyint(4) NOT NULL DEFAULT 0,
  `perso_amelioration` tinyint(1) NOT NULL DEFAULT 0,
  `fid_joueur_arme_joueur` bigint(20) unsigned DEFAULT NULL,
  `fid_joueur_arme_arme` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`fid_joueur`,`fid_perso`),
  KEY `fk_jp_perso` (`fid_perso`),
  CONSTRAINT `fk_jp_joueur` FOREIGN KEY (`fid_joueur`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jp_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mate_ennemi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mate_ennemi` (
  `fid_materiaux` int(10) unsigned NOT NULL,
  `fid_ennemi` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fid_materiaux`,`fid_ennemi`),
  KEY `fk_me_ennemi` (`fid_ennemi`),
  CONSTRAINT `fk_me_ennemi` FOREIGN KEY (`fid_ennemi`) REFERENCES `ennemi` (`id_ennemi`) ON DELETE CASCADE,
  CONSTRAINT `fk_me_mat` FOREIGN KEY (`fid_materiaux`) REFERENCES `materiaux` (`id_materiaux`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `materiaux`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materiaux` (
  `id_materiaux` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_mat` varchar(150) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descri_mat` text DEFAULT NULL,
  `fid_typeM` int(10) unsigned NOT NULL,
  `fid_rareté` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_materiaux`),
  UNIQUE KEY `materiaux_slug_unique` (`slug`),
  KEY `fk_mat_type` (`fid_typeM`),
  KEY `fk_mat_rarete` (`fid_rareté`),
  CONSTRAINT `fk_mat_rarete` FOREIGN KEY (`fid_rareté`) REFERENCES `rareté` (`id_rareté`),
  CONSTRAINT `fk_mat_type` FOREIGN KEY (`fid_typeM`) REFERENCES `type_materiaux` (`id_typeM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `motus_score`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motus_score` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid_joueur` bigint(20) unsigned NOT NULL,
  `date_partie` date NOT NULL,
  `mot` varchar(150) NOT NULL,
  `nb_essais` tinyint(4) DEFAULT NULL,
  `gagne` tinyint(1) NOT NULL DEFAULT 0,
  `mode` enum('journalier','libre') NOT NULL DEFAULT 'journalier',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_motus_joueur_date` (`fid_joueur`,`date_partie`,`mode`),
  CONSTRAINT `motus_score_ibfk_1` FOREIGN KEY (`fid_joueur`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nation` (
  `id_region` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_region` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descri_region` text DEFAULT NULL,
  PRIMARY KEY (`id_region`),
  UNIQUE KEY `nation_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personnage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnage` (
  `id_perso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_perso` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `block_order` varchar(255) NOT NULL DEFAULT 'main_zone,armes,artefacts,constellations,competences',
  `affinite_perso` varchar(50) DEFAULT NULL,
  `fid_TP` int(10) unsigned NOT NULL,
  `fid_etoile` int(10) unsigned NOT NULL,
  `fid_element` int(10) unsigned NOT NULL,
  `fid_TArmes` int(10) unsigned NOT NULL,
  `arme_icon` varchar(255) DEFAULT NULL,
  `background_actif` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_perso`),
  UNIQUE KEY `personnage_slug_unique` (`slug`),
  KEY `fk_perso_type` (`fid_TP`),
  KEY `fk_perso_etoile` (`fid_etoile`),
  KEY `fk_perso_element` (`fid_element`),
  KEY `fk_perso_tarmes` (`fid_TArmes`),
  CONSTRAINT `fk_perso_element` FOREIGN KEY (`fid_element`) REFERENCES `elements` (`id_element`),
  CONSTRAINT `fk_perso_etoile` FOREIGN KEY (`fid_etoile`) REFERENCES `etoile` (`id_etoile`),
  CONSTRAINT `fk_perso_tarmes` FOREIGN KEY (`fid_TArmes`) REFERENCES `type_armes` (`id_TArmes`),
  CONSTRAINT `fk_perso_type` FOREIGN KEY (`fid_TP`) REFERENCES `type_perso` (`id_TP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personnage_a_monter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnage_a_monter` (
  `fid_joueur` bigint(20) unsigned NOT NULL,
  `fid_perso` int(10) unsigned NOT NULL,
  `fid_materiaux` int(10) unsigned NOT NULL,
  `quantite` int(10) unsigned NOT NULL DEFAULT 0,
  `type` enum('niveau','competence') NOT NULL,
  PRIMARY KEY (`fid_joueur`,`fid_perso`,`fid_materiaux`,`type`),
  KEY `fid_materiaux` (`fid_materiaux`),
  CONSTRAINT `personnage_a_monter_ibfk_1` FOREIGN KEY (`fid_joueur`, `fid_perso`) REFERENCES `joueur_personnage` (`fid_joueur`, `fid_perso`) ON DELETE CASCADE,
  CONSTRAINT `personnage_a_monter_ibfk_2` FOREIGN KEY (`fid_materiaux`) REFERENCES `materiaux` (`id_materiaux`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personnage_arme_recommandee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnage_arme_recommandee` (
  `fid_perso` int(10) unsigned NOT NULL,
  `fid_arme` int(10) unsigned NOT NULL,
  `position` tinyint(4) NOT NULL,
  `origine` enum('tirage','evenement','craft','achat') DEFAULT NULL,
  `starter` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`fid_perso`,`fid_arme`),
  UNIQUE KEY `uk_arme_position` (`fid_perso`,`position`),
  KEY `personnage_arme_recommandee_fid_arme_foreign` (`fid_arme`),
  CONSTRAINT `personnage_arme_recommandee_fid_arme_foreign` FOREIGN KEY (`fid_arme`) REFERENCES `armes` (`id_arme`) ON DELETE CASCADE,
  CONSTRAINT `personnage_arme_recommandee_fid_perso_foreign` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personnage_artefact_recommande`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnage_artefact_recommande` (
  `id_build` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid_perso` int(10) unsigned NOT NULL,
  `fid_artefact_1` int(10) unsigned NOT NULL,
  `pieces_1` enum('2p','4p') NOT NULL DEFAULT '4p',
  `fid_artefact_2` int(10) unsigned DEFAULT NULL,
  `pieces_2` enum('2p') DEFAULT NULL,
  `position` tinyint(4) NOT NULL,
  PRIMARY KEY (`id_build`),
  UNIQUE KEY `uk_build_position` (`fid_perso`,`position`),
  KEY `fk_pab_perso` (`fid_perso`),
  KEY `fk_pab_art1` (`fid_artefact_1`),
  KEY `fk_pab_art2` (`fid_artefact_2`),
  CONSTRAINT `fk_pab_art1` FOREIGN KEY (`fid_artefact_1`) REFERENCES `artefact` (`id_artefact`) ON DELETE CASCADE,
  CONSTRAINT `fk_pab_art2` FOREIGN KEY (`fid_artefact_2`) REFERENCES `artefact` (`id_artefact`) ON DELETE SET NULL,
  CONSTRAINT `fk_pab_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personnage_artefact_recommandee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnage_artefact_recommandee` (
  `id_build` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid_perso` int(10) unsigned NOT NULL,
  `fid_artefact_1` int(10) unsigned NOT NULL,
  `pieces_1` enum('2p','4p') NOT NULL DEFAULT '4p',
  `fid_artefact_2` int(10) unsigned DEFAULT NULL,
  `pieces_2` enum('2p') DEFAULT NULL,
  `main_stat_sablier` varchar(120) DEFAULT NULL,
  `main_stat_gobelet` varchar(120) DEFAULT NULL,
  `main_stat_couronne` varchar(120) DEFAULT NULL,
  `sub_stats` varchar(255) DEFAULT NULL,
  `position` tinyint(4) NOT NULL,
  PRIMARY KEY (`id_build`),
  UNIQUE KEY `uk_build_position` (`fid_perso`,`position`),
  KEY `personnage_artefact_recommandee_fid_artefact_1_foreign` (`fid_artefact_1`),
  KEY `personnage_artefact_recommandee_fid_artefact_2_foreign` (`fid_artefact_2`),
  CONSTRAINT `personnage_artefact_recommandee_fid_artefact_1_foreign` FOREIGN KEY (`fid_artefact_1`) REFERENCES `artefact` (`id_artefact`) ON DELETE CASCADE,
  CONSTRAINT `personnage_artefact_recommandee_fid_artefact_2_foreign` FOREIGN KEY (`fid_artefact_2`) REFERENCES `artefact` (`id_artefact`) ON DELETE SET NULL,
  CONSTRAINT `personnage_artefact_recommandee_fid_perso_foreign` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personnage_nation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnage_nation` (
  `fid_perso` int(10) unsigned NOT NULL,
  `fid_nation` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fid_perso`,`fid_nation`),
  KEY `fk_pn_nation` (`fid_nation`),
  CONSTRAINT `fk_pn_nation` FOREIGN KEY (`fid_nation`) REFERENCES `nation` (`id_region`) ON DELETE CASCADE,
  CONSTRAINT `fk_pn_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personnage_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnage_role` (
  `fid_perso` int(10) unsigned NOT NULL,
  `fid_role` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fid_perso`,`fid_role`),
  KEY `fk_pr_role` (`fid_role`),
  CONSTRAINT `fk_pr_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE,
  CONSTRAINT `fk_pr_role` FOREIGN KEY (`fid_role`) REFERENCES `role` (`id_role`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personnage_video`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnage_video` (
  `id_video` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid_perso` int(10) unsigned NOT NULL,
  `url_video` varchar(255) NOT NULL,
  `ordre` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_video`),
  KEY `personnage_video_fid_perso_foreign` (`fid_perso`),
  CONSTRAINT `personnage_video_fid_perso_foreign` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `photo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `photo` (
  `id_photo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chemin_photo` varchar(255) NOT NULL,
  `photoable_type` varchar(100) NOT NULL,
  `photoable_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `source_url` varchar(500) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_photo`),
  KEY `idx_photoable` (`photoable_type`,`photoable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plat` (
  `id_plat` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_plat` varchar(150) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descri_plat` text DEFAULT NULL,
  `fid_rareté` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_plat`),
  UNIQUE KEY `plat_slug_unique` (`slug`),
  KEY `fk_plat_rarete` (`fid_rareté`),
  CONSTRAINT `fk_plat_rarete` FOREIGN KEY (`fid_rareté`) REFERENCES `rareté` (`id_rareté`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plat_ingredient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plat_ingredient` (
  `fid_plat` int(10) unsigned NOT NULL,
  `fid_ingredient` int(10) unsigned NOT NULL,
  `quantite` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`fid_plat`,`fid_ingredient`),
  KEY `fk_pi_ingre` (`fid_ingredient`),
  CONSTRAINT `fk_pi_ingre` FOREIGN KEY (`fid_ingredient`) REFERENCES `ingrédient` (`id_ingredient`) ON DELETE CASCADE,
  CONSTRAINT `fk_pi_plat` FOREIGN KEY (`fid_plat`) REFERENCES `plat` (`id_plat`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post` (
  `id_post` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titre_post` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `corps_post` longtext NOT NULL,
  `image_cover` varchar(255) DEFAULT NULL,
  `statut` enum('brouillon','publié') NOT NULL DEFAULT 'brouillon',
  `fid_admin` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_post`),
  UNIQUE KEY `post_slug_unique` (`slug`),
  KEY `fk_post_admin` (`fid_admin`),
  CONSTRAINT `fk_post_admin` FOREIGN KEY (`fid_admin`) REFERENCES `admin` (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produits` (
  `id_produit` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_produit` varchar(150) NOT NULL,
  `descri_produit` text DEFAULT NULL,
  `fid_region` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_produit`),
  KEY `fk_prod_nation` (`fid_region`),
  CONSTRAINT `fk_prod_nation` FOREIGN KEY (`fid_region`) REFERENCES `nation` (`id_region`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `provenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provenance` (
  `id_provenance` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_provenance` varchar(100) NOT NULL,
  PRIMARY KEY (`id_provenance`),
  UNIQUE KEY `uk_provenance_libelle` (`libelle_provenance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rareté`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rareté` (
  `id_rareté` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_rareté` varchar(30) NOT NULL,
  PRIMARY KEY (`id_rareté`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reaction` (
  `id_reaction` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nom_reaction` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_reaction`),
  UNIQUE KEY `reaction_nom_reaction_unique` (`nom_reaction`),
  UNIQUE KEY `reaction_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `id_role` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_role` varchar(50) NOT NULL,
  `descri_role` text DEFAULT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `role_libelle_unique` (`libelle_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sous_region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sous_region` (
  `id_sous_region` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_sous_region` varchar(150) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `fid_region` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_sous_region`),
  UNIQUE KEY `sous_region_slug_unique` (`slug`),
  KEY `fk_sr_nation` (`fid_region`),
  CONSTRAINT `fk_sr_nation` FOREIGN KEY (`fid_region`) REFERENCES `nation` (`id_region`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `spécialité`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spécialité` (
  `id_specialite` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_spe` varchar(150) DEFAULT NULL,
  `descri_spe` text DEFAULT NULL,
  `fid_plat` int(10) unsigned NOT NULL,
  `fid_perso` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_specialite`),
  UNIQUE KEY `uk_spe_plat` (`fid_plat`),
  KEY `fk_spe_perso` (`fid_perso`),
  CONSTRAINT `fk_spe_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE,
  CONSTRAINT `fk_spe_plat` FOREIGN KEY (`fid_plat`) REFERENCES `plat` (`id_plat`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `team_composition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_composition` (
  `id_team` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid_perso` int(10) unsigned NOT NULL,
  `type_reaction` varchar(60) DEFAULT NULL,
  `tag` enum('recommended','f2p') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_team`),
  UNIQUE KEY `uk_team_reaction_tag` (`fid_perso`,`type_reaction`,`tag`),
  KEY `idx_team_perso` (`fid_perso`),
  KEY `idx_team_reaction` (`type_reaction`),
  CONSTRAINT `fk_team_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `team_composition_membre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_composition_membre` (
  `id_membre` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid_team` int(10) unsigned NOT NULL,
  `fid_perso` int(10) unsigned NOT NULL,
  `slot` tinyint(3) unsigned NOT NULL,
  `role_override` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_membre`),
  UNIQUE KEY `uk_team_slot` (`fid_team`,`slot`),
  UNIQUE KEY `uk_team_perso_once` (`fid_team`,`fid_perso`),
  KEY `fk_team_membre_perso` (`fid_perso`),
  KEY `idx_team_membre` (`fid_team`),
  CONSTRAINT `fk_team_membre` FOREIGN KEY (`fid_team`) REFERENCES `team_composition` (`id_team`) ON DELETE CASCADE,
  CONSTRAINT `fk_team_membre_perso` FOREIGN KEY (`fid_perso`) REFERENCES `personnage` (`id_perso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `team_slot_remplacant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_slot_remplacant` (
  `id_remplacant` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid_team` int(10) unsigned NOT NULL,
  `slot` tinyint(3) unsigned NOT NULL,
  `fid_perso_remplacant` int(10) unsigned NOT NULL,
  `role_override` varchar(100) DEFAULT NULL,
  `ordre` smallint(5) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_remplacant`),
  UNIQUE KEY `uk_team_slot_remplacant` (`fid_team`,`slot`,`fid_perso_remplacant`),
  KEY `fk_remplacant_perso` (`fid_perso_remplacant`),
  KEY `idx_remplacant_slot` (`fid_team`,`slot`),
  CONSTRAINT `fk_remplacant_perso` FOREIGN KEY (`fid_perso_remplacant`) REFERENCES `personnage` (`id_perso`),
  CONSTRAINT `fk_remplacant_team` FOREIGN KEY (`fid_team`) REFERENCES `team_composition` (`id_team`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_animal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_animal` (
  `id_TAnimal` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_TAnimal` varchar(50) NOT NULL,
  PRIMARY KEY (`id_TAnimal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_apti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_apti` (
  `id_TypeApti` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_Apti` varchar(50) NOT NULL,
  PRIMARY KEY (`id_TypeApti`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_armes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_armes` (
  `id_TArmes` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_TArme` varchar(50) NOT NULL,
  PRIMARY KEY (`id_TArmes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_ennemi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_ennemi` (
  `id_typeEnnemi` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_Type` varchar(50) NOT NULL,
  PRIMARY KEY (`id_typeEnnemi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_materiaux`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_materiaux` (
  `id_typeM` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_TypeM` varchar(50) NOT NULL,
  PRIMARY KEY (`id_typeM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_perso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_perso` (
  `id_TP` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle_TP` varchar(50) NOT NULL,
  PRIMARY KEY (`id_TP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `pseudo` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `oauth_provider` enum('discord') DEFAULT NULL,
  `oauth_id` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `banniere` varchar(255) DEFAULT NULL,
  `bio_joueur` text DEFAULT NULL,
  `uid_genshin` varchar(20) DEFAULT NULL,
  `date_inscription` datetime NOT NULL DEFAULT current_timestamp(),
  `derniere_connexion` datetime DEFAULT NULL,
  `banni_le` datetime DEFAULT NULL,
  `motif_ban` text DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_pseudo_unique` (`pseudo`),
  UNIQUE KEY `users_uid_genshin_unique` (`uid_genshin`),
  UNIQUE KEY `uq_oauth` (`oauth_provider`,`oauth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_03_01_000001_create_etoile_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_03_01_000002_create_elements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_03_01_000003_create_type_armes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_03_01_000004_create_type_perso_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_03_01_000005_create_personnages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_03_01_000006_add_joueur_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_03_01_000007_create_rarete_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_03_01_000008_create_type_apti_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_03_01_000009_create_type_ennemi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_03_01_000010_create_type_animal_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_03_01_000011_create_type_materiaux_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_03_01_000012_create_admin_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_03_01_000013_create_bio_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_03_01_000014_create_constellation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_03_01_000015_create_aptitude_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_03_01_000016_create_evenements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_03_01_000017_create_photos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_03_01_000018_create_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_03_01_000019_create_personnage_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_03_01_000020_create_armes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_03_01_000021_create_arm_stats_rang_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_03_01_000022_create_arm_stats_niveau_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_03_01_000023_create_joueur_arme_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_03_01_000024_create_joueur_personnage_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_03_01_000025_create_joueur_constellation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_03_01_000026_create_amitie_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_03_01_000027_create_region_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_03_01_000028_create_sous_region_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_03_01_000029_create_produits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_03_01_000030_create_ennemi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_03_01_000031_create_ennemi_region_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_03_01_000032_create_materiaux_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_03_01_000033_create_mate_ennemi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_03_01_000034_create_animaux_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_03_01_000035_create_animal_region_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_03_01_000036_create_ingredient_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_03_01_000037_create_animal_ingredient_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_03_01_000038_create_plat_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_03_01_000039_create_specialite_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_03_01_000040_create_plat_ingredient_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_03_01_000041_create_chronologie_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_03_02_000001_add_type_to_photo_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_03_22_164351_normalize_type_perso_values',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_03_25_150441_create_personnage_video_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_03_25_150442_add_block_order_to_personnage_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_03_25_150442_create_artefact_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_03_25_150442_create_personnage_arme_recommandee_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_03_25_150442_create_personnage_artefact_recommandee_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_03_25_154448_create_personnage_nation_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_04_09_000001_redesign_team_tables',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_03_28_135437_add_arme_icon_to_personnage_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_04_04_000001_add_positions_const_to_constellation_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_04_09_000001_create_team_composition_tables',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_04_09_200001_create_team_composition_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_04_09_200002_create_team_composition_membre_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_04_09_200003_create_team_slot_remplacant_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_04_09_210000_create_reaction_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_04_14_121000_create_histoire_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_04_14_123000_add_titre_histoire_to_histoire_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_04_14_130000_create_blog_article_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_04_15_090000_create_blog_slug_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_04_17_000000_add_permissions_to_admin_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_04_17_100000_add_two_factor_to_users_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_04_17_100001_add_two_factor_to_admin_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_04_17_150000_add_profile_images_to_admin_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_04_22_120000_add_stat_fields_to_personnage_artefact_recommandee_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_04_29_203758_create_permission_tables',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2026_04_29_212500_rename_admin_permissions_to_legacy_permissions',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2026_04_29_211500_migrate_admin_permissions_to_spatie',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_04_15_110000_add_layout_json_to_blog_article_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_04_19_000000_migrate_blog_content_to_layout',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_05_01_201707_create_articles_table',29);
