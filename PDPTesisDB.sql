-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 23-05-2024 a las 13:30:27
-- Versión del servidor: 10.4.20-MariaDB
-- Versión de PHP: 8.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `PDPTesisDB`
--
DROP DATABASE IF EXISTS `PDPTesisDB`;
CREATE DATABASE IF NOT EXISTS `PDPTesisDB` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `PDPTesisDB`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblAreas`
--

DROP TABLE IF EXISTS `tblAreas`;
CREATE TABLE `tblAreas` (
  `AreaId` tinyint(2) UNSIGNED NOT NULL,
  `InstitutionId` smallint(4) UNSIGNED NOT NULL,
  `AreaName` varchar(48) COLLATE utf8_unicode_ci NOT NULL,
  `AreaStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Areas table';

--
-- Volcado de datos para la tabla `tblAreas`
--

INSERT INTO `tblAreas` (`AreaId`, `InstitutionId`, `AreaName`, `AreaStatusId`) VALUES
(1, 1, 'Sistemas', 1),
(2, 1, 'Ginecología', 1),
(3, 1, 'Obstetricia', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblCases`
--

DROP TABLE IF EXISTS `tblCases`;
CREATE TABLE `tblCases` (
  `CaseId` mediumint(7) UNSIGNED NOT NULL,
  `PatientId` mediumint(7) UNSIGNED NOT NULL,
  `CaseDate` date NOT NULL,
  `LastMenstrualPeriod` date NOT NULL,
  `InitialBloodPressure` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `InitialWeight` tinyint(3) UNSIGNED NOT NULL,
  `InitialSymptoms` text COLLATE utf8_unicode_ci NOT NULL,
  `DeliveryDate` date DEFAULT NULL,
  `CaseNotes` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `UserId` smallint(4) UNSIGNED NOT NULL,
  `CaseStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblCasesConsultations`
--

DROP TABLE IF EXISTS `tblCasesConsultations`;
CREATE TABLE `tblCasesConsultations` (
  `CaseConsultationId` mediumint(7) UNSIGNED NOT NULL,
  `CaseId` mediumint(7) UNSIGNED NOT NULL,
  `CaseConsultationDate` date NOT NULL,
  `CurrentBloodPressure` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `CurrentWeight` tinyint(3) UNSIGNED NOT NULL,
  `CurrentSymptoms` text COLLATE utf8_unicode_ci NOT NULL,
  `CaseConsultationNotes` text COLLATE utf8_unicode_ci NOT NULL,
  `CaseConsultationStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblCasesConsultationsDx`
--

DROP TABLE IF EXISTS `tblCasesConsultationsDx`;
CREATE TABLE `tblCasesConsultationsDx` (
  `CaseConsultationDxId` int(9) UNSIGNED NOT NULL,
  `CaseConsultationId` mediumint(7) UNSIGNED NOT NULL,
  `CaseConsultationDxCIE11` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `CaseConsultationDxDescription` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `CaseConsultationDxStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblCasesExplorations`
--

DROP TABLE IF EXISTS `tblCasesExplorations`;
CREATE TABLE `tblCasesExplorations` (
  `CaseExplorationId` mediumint(7) UNSIGNED NOT NULL,
  `CaseId` mediumint(7) UNSIGNED NOT NULL,
  `CaseExplorationDate` date NOT NULL,
  `CaseExplorationNotes` text COLLATE utf8_unicode_ci NOT NULL,
  `CaseExplorationStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblCasesExplorationsParams`
--

DROP TABLE IF EXISTS `tblCasesExplorationsParams`;
CREATE TABLE `tblCasesExplorationsParams` (
  `CaseExplorationParamId` int(9) UNSIGNED NOT NULL,
  `CaseExplorationId` mediumint(7) UNSIGNED NOT NULL,
  `CaseExplorationParam` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `CaseExplorationParamResult` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `CaseExplorationParamStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblCasesLabtests`
--

DROP TABLE IF EXISTS `tblCasesLabtests`;
CREATE TABLE `tblCasesLabtests` (
  `CaseLabtestId` mediumint(7) UNSIGNED NOT NULL,
  `CaseId` mediumint(7) UNSIGNED NOT NULL,
  `CaseLabtestDate` date NOT NULL,
  `CaseLabtestNotes` text COLLATE utf8_unicode_ci NOT NULL,
  `CaseLabtestStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblCasesLabtestsParams`
--

DROP TABLE IF EXISTS `tblCasesLabtestsParams`;
CREATE TABLE `tblCasesLabtestsParams` (
  `CaseLabtestParamId` int(9) UNSIGNED NOT NULL,
  `CaseLabtestId` mediumint(7) UNSIGNED NOT NULL,
  `CaseLabtestParam` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `CaseLabtestParamResult` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `CaseLabtestParamStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblCPCategories`
--

DROP TABLE IF EXISTS `tblCPCategories`;
CREATE TABLE `tblCPCategories` (
  `CPCategoryId` tinyint(1) UNSIGNED NOT NULL,
  `CPCategoryName` varchar(48) COLLATE utf8_unicode_ci NOT NULL,
  `CPCategoryStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `tblCPCategories`
--

INSERT INTO `tblCPCategories` (`CPCategoryId`, `CPCategoryName`, `CPCategoryStatusId`) VALUES
(1, 'Antecedentes Patológicos Hereditarios', 1),
(2, 'Antecedentes Personales Patológicos', 1),
(3, 'Antecedentes Personales No Patológicos', 1),
(4, 'Alergias', 1),
(5, 'Interrogatorio Por Aparato', 1),
(7, 'Otros', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblCPFields`
--

DROP TABLE IF EXISTS `tblCPFields`;
CREATE TABLE `tblCPFields` (
  `CPFieldId` smallint(3) UNSIGNED NOT NULL,
  `CPCategoryId` tinyint(1) UNSIGNED NOT NULL,
  `CPFieldName` varchar(48) COLLATE utf8_unicode_ci NOT NULL,
  `CPFieldText` text COLLATE utf8_unicode_ci NOT NULL,
  `CPFieldStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `tblCPFields`
--

INSERT INTO `tblCPFields` (`CPFieldId`, `CPCategoryId`, `CPFieldName`, `CPFieldText`, `CPFieldStatusId`) VALUES
(1, 1, 'Parentesco: Padres', 'Indicar si alguno de los padres padece o padeció Hipertensión, Diabetes, Cancer, Enfermedades Cardiacas, Enfermedades Genéticas o Enfermedades Neurológicas.', 1),
(2, 1, 'Parentesco: Hermanos', 'Indicar si alguno de los hermanos padece o padeció Hipertensión, Diabetes, Cancer, Enfermedades Cardiacas, Enfermedades Genéticas o Enfermedades Neurológicas.', 1),
(3, 1, 'Parentesco: Hijos', 'Indicar si alguno de los hijos padece o padeció Hipertensión, Diabetes, Cancer, Enfermedades Cardiacas, Enfermedades Genéticas o Enfermedades Neurológicas.', 1),
(4, 1, 'Parentesco: Abuelos', 'Indicar si alguno de los abuelos padece o padeció Hipertensión, Diabetes, Cancer, Enfermedades Cardiacas, Enfermedades Genéticas o Enfermedades Neurológicas.', 1),
(5, 1, 'Parentesco: Tíos', 'Indicar si alguno de los tíos padece o padeció Hipertensión, Diabetes, Cancer, Enfermedades Cardiacas, Enfermedades Genéticas o Enfermedades Neurológicas.', 1),
(6, 2, 'Personal: Diabetes', 'Indicar si la paciente padece Diabetes y describir detalles.', 1),
(7, 2, 'Personal: Hipertensión', 'Indicar si la paciente padece Hipertensión y describir detalles.', 1),
(8, 2, 'Personal: Cardiopatías', 'Indicar si la paciente padece Cardiopatías y describir detalles.', 1),
(9, 2, 'Personal: Asma Bronquial', 'Indicar si la paciente padece Asma Bronquial y describir detalles.', 1),
(10, 2, 'Personal: Degenerativas o Malignas', 'Indicar si la paciente padece condiciones Degenerativas o Malignas y describir detalles.', 1),
(11, 2, 'Personal: Intervenciones Quirúrgicas', 'Indicar si la paciente ha recibido Intervenciones Quirúrgicas y describir detalles.', 1),
(12, 2, 'Personal: Traumáticas', 'Indicar si la paciente padece o ha padecido de alguna enfermedad Traumática y describir detalles.', 1),
(13, 2, 'Personal: Alérgicas', 'Indicar si la paciente ha padecido alguna enfermedad por reacciones alérgicas y describir detalles.', 1),
(14, 2, 'Personal: Parasitarías', 'Indicar si la paciente padece o ha padecido de alguna enfermedad Parasitaria y describir detalles.', 1),
(15, 2, 'Personal: Infecciosas', 'Indicar si la paciente padece o ha padecido de alguna enfermedad Infecciosa y describir detalles.', 1),
(16, 2, 'Personal: VIH', 'Indicar si la paciente padece VIH y describir detalles.', 1),
(17, 2, 'Personal: Herpes', 'Indicar si la paciente padece Herpes y describir detalles.', 1),
(18, 2, 'Personal: Hepatitis', 'Indicar si la paciente padece o ha padecido Hepatitis y describir detalles.', 1),
(19, 2, 'Personal: Papiloma', 'Indicar si la paciente padece o ha padecido Papiloma y describir detalles.', 1),
(20, 2, 'Personal: Tuberculosis', 'Indicar si la paciente padece o ha padecido Tuberculosis y describir detalles.', 1),
(21, 2, 'Personal: Artritis', 'Indicar si la paciente padece Artritis y describir detalles.', 1),
(22, 2, 'Personal: Epilepsia', 'Indicar si la paciente padece Epilepsia y describir detalles.', 1),
(23, 2, 'Personal: Intoxicaciones', 'Indicar si la paciente ha padecido Intoxicaciones y describir detalles.', 1),
(24, 2, 'Personal: Embarazo', 'Indicar si la paciente se encuentra Embarazada y describir detalles.', 1),
(25, 2, 'Personal: Transfusiones', 'Indicar si la paciente ha recibido transfusiones sanguíneas y describir detalles.', 1),
(26, 2, 'Personal: Típicas de la Niñez', 'Indicar si la paciente ha padecido Enfermedades Típicas de la Niñez y describir detalles.', 1),
(27, 2, 'Personal: Adicciones Pasadas', 'Indicar si la paciente es exfumadora, exalcohólica o exadicta a alguna droga y describir detalles.', 1),
(28, 2, 'Personal: Otros', 'Indicar alguna otra enfermedad o condición de salud que padezca o haya padecido la paciente y describir detalles.', 1),
(29, 3, 'Higiene: Vestuario', 'Indicar los hábitos de higiene en el vestuario de la paciente.', 1),
(30, 3, 'Higiene: Corporal', 'Indicar los hábitos de higiene en el cuerpo de la paciente.', 1),
(31, 3, 'Higiene: Dental', 'Indicar los hábitos de higiene en los dientes de la paciente.', 1),
(32, 3, 'Hábitos Alimenticios', 'Indicar los hábitos alimenticios de la paciente.', 1),
(33, 3, 'Hábitos de Actividad Física', 'Indicar los hábitos en actividad física de la paciente.', 1),
(34, 3, 'Antecedente de Hospitalización', 'Indicar si la paciente ha sido hospitalizada e indicar detalles.', 1),
(35, 4, 'Antibióticos', 'Indicar si la paciente es alérgica a algún tipo de antibiótico.', 1),
(36, 4, 'Analgésicos', 'Indicar si la paciente es alérgica a algún tipo de analgésico.', 1),
(37, 4, 'Anestésicos', 'Indicar si la paciente es alérgica a algún tipo de anestésico.', 1),
(38, 4, 'Alimentos', 'Indicar si la paciente es alérgica a algún tipo de alimento.', 1),
(39, 4, 'Medio Ambiente', 'Indicar si la paciente es alérgica a algún tipo de factor ambiental.', 1),
(40, 5, 'Aparato Respiratorio', 'Indicar si padece alguna enfermedad del aparato respiratorio como: Rinorrea, Tos, Expectoración, Sibilancias, Fiebre, Disnea, Disfonía, Dolor Torácico, Epistaxis, Hemoptisis, Cianosis, etc.', 1),
(41, 5, 'Aparato Digestivo', 'Indicar si padece alguna enfermedad del aparato digestivo como: Anorexia, Polifagia, Bulimia, Polidipsia, Nauseas, Vómito, Regurgitación, Disfagia, Hematemesis, Pirosis, Flatulencia, Eructos, Melena, Diarrea, Estreñimiento, Ictericia, etc.', 1),
(42, 5, 'Aparato Cardiovascular', 'Indicar si padece alguna enfermedad del aparato cardiovascular como: Palpitaciones, Dolor Torácico, Lipotimia, Síncope, Disnea, Diaforesis, Apnea, Cianosis, Edema, Acúfenos, Taquicardia, Fosfenos, Astenia, etc.', 1),
(43, 5, 'Aparato Genitorinario', 'Indicar si padece alguna enfermedad del aparato genitorinario como: Disuria, Anuria, Poliuria, Oliguria, Nicturia, Hematuria, Coluria, Incontinencia, etc.', 1),
(44, 5, 'Sistema Endocrino', 'Indicar si padece alguna enfermedad del sistema endocrino como: Pérdida o aumento de peso, Fatiga o Hiperactividad, Intolerancia al Frío o al Calor, Nerviosismo, Insomnio o Somnolencia, Temblor de manos, Ansiedad, Bocio, Exoftalmos, Polifagia, Polidipsia, Poliuria, etc.', 1),
(45, 5, 'Sistema Hemático y Linfático', 'Indicar si padece alguna enfermedad del sistema hemático y linfático como: Hemorragias, Epistaxis, Palidez, Disnea, Fatigabilidad, Astenia, Sangrado fácil, Petequias, Hematomas, Adenomegalia, etc.', 1),
(46, 5, 'Sistema Musculoesquelético', 'Indicar si padece alguna enfermedad del sistema musculoesquelético como: Mialgia, Deformidad articular, Dolor articular, Limitación de movimientos, Trastorno de la marcha, etc.', 1),
(47, 5, 'Sistema Nervioso', 'Indicar si padece alguna enfermedad del sistema nervioso como: Cefalea, Síncope, Convulsiones, Vértigo, Confusión, Parestesías, Parálisis, Lipotimia, Temblor, Trastornos de la marcha y equilibrio, Cambio de ciclo de sueño, Problemas en el habla, etc.', 1),
(48, 5, 'Sistema Tegumentario', 'Indicar si padece alguna enfermedad del sistema tegumentario como: Cambio de color de piel, Eritema, Prurito, Cambio en lunares, Presencia de piercings o Tatuajes, Pérdida de pelo o vello, Cutis seco, Erupciones, etc.', 1),
(49, 5, 'Esfera Psiquica', 'Indicar si padece algún padecimiento de la esfera psiquica como: Depresión, Angustia, Ansiedad, Trastorno de sueño, etc.', 1),
(50, 7, 'Otros antecedentes de historia clínica', 'Indicar si existe algún otro antecedente de importancia para el registro en la historia clínica de la paciente, que no se entre en ninguna de las categorías anteriores.', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblInstitutions`
--

DROP TABLE IF EXISTS `tblInstitutions`;
CREATE TABLE `tblInstitutions` (
  `InstitutionId` smallint(4) UNSIGNED NOT NULL,
  `InstitutionName` varchar(48) COLLATE utf8_unicode_ci NOT NULL,
  `InstitutionShortname` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `InstitutionStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Institutions table';

--
-- Volcado de datos para la tabla `tblInstitutions`
--

INSERT INTO `tblInstitutions` (`InstitutionId`, `InstitutionName`, `InstitutionShortname`, `InstitutionStatusId`) VALUES
(1, 'ISSSTECALI TIJUANA', 'I-TJ', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblModules`
--

DROP TABLE IF EXISTS `tblModules`;
CREATE TABLE `tblModules` (
  `ModuleId` tinyint(2) UNSIGNED NOT NULL,
  `ModuleName` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `ModuleDescription` text COLLATE utf8_unicode_ci NOT NULL,
  `ModuleStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Modules table';

--
-- Volcado de datos para la tabla `tblModules`
--

INSERT INTO `tblModules` (`ModuleId`, `ModuleName`, `ModuleDescription`, `ModuleStatusId`) VALUES
(1, 'DASHBOARD', 'Main Dashboard', 1),
(2, 'ADMINISTRATION', 'Administration Modules', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblModulesOptions`
--

DROP TABLE IF EXISTS `tblModulesOptions`;
CREATE TABLE `tblModulesOptions` (
  `ModuleOptionId` smallint(4) UNSIGNED NOT NULL,
  `ModuleId` tinyint(2) UNSIGNED NOT NULL,
  `ModuleOptionName` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `ModuleOptionDescription` text COLLATE utf8_unicode_ci NOT NULL,
  `ModuleOptionStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Modules options table';

--
-- Volcado de datos para la tabla `tblModulesOptions`
--

INSERT INTO `tblModulesOptions` (`ModuleOptionId`, `ModuleId`, `ModuleOptionName`, `ModuleOptionDescription`, `ModuleOptionStatusId`) VALUES
(1, 1, 'DASHBOARD-*', 'Dashboard All Access', 1),
(2, 1, 'DASHBOARD-%', 'Dashboard Module Access', 1),
(3, 1, 'DASHBOARD-SECTION_1', 'Dashboard Section 1 Access', 1),
(4, 2, 'ADMINISTRATION-*', 'Administration All Access', 1),
(5, 2, 'ADMINISTRATION-%', 'Administration Module Access', 1),
(6, 2, 'ADMINISTRATION-CATALOG_LIST_GENERAL', 'Administration General Catalog Access', 1),
(7, 2, 'ADMINISTRATION-CATALOG_LIST_EHR', 'Administration EHR Catalog Access', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblPatients`
--

DROP TABLE IF EXISTS `tblPatients`;
CREATE TABLE `tblPatients` (
  `PatientId` mediumint(7) UNSIGNED NOT NULL,
  `PatientAffiliationId` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PatientFirstName` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PatientLastName` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PatientBirthDate` date NOT NULL,
  `PatientBloodType` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PatientObservations` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `PatientStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblPatients_CPFields`
--

DROP TABLE IF EXISTS `tblPatients_CPFields`;
CREATE TABLE `tblPatients_CPFields` (
  `Patient_CPFieldId` mediumint(7) UNSIGNED NOT NULL,
  `PatientId` mediumint(7) UNSIGNED NOT NULL,
  `CPFieldId` smallint(4) UNSIGNED NOT NULL,
  `Patient_CPFieldNote` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `Patient_CPFieldCreatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Patient_CPFieldStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblUsers`
--

DROP TABLE IF EXISTS `tblUsers`;
CREATE TABLE `tblUsers` (
  `UserId` smallint(4) UNSIGNED NOT NULL,
  `Username` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `Password` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `Email` varchar(48) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PhoneNumber` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FirstName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `LastName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `AreaId` tinyint(2) UNSIGNED NOT NULL,
  `Token` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TokenExpiryDateTime` datetime DEFAULT NULL,
  `UserStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Users table';

--
-- Volcado de datos para la tabla `tblUsers`
--

INSERT INTO `tblUsers` (`UserId`, `Username`, `Password`, `Email`, `PhoneNumber`, `FirstName`, `LastName`, `AreaId`, `Token`, `TokenExpiryDateTime`, `UserStatusId`) VALUES
(1, 'piloto', '$2y$10$Qv3LMsIwg67be4Nwaf/a4uU.mWtDWIX8Sw7sJpFlsfHNI./7tNRpS', 'piloto@piloto.com', '6969', 'USUARIO', 'PILOTO', 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJJUDIwIiwiZXhwIjoiMjAyMzExMjAxNjM5NTEiLCJqdGkiOiIxIn0.iT181dgxMVyeRJjBkryNwvC-Y3wUJb1UH3WbBjpeTu4', '2023-11-20 16:39:51', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblUsers_ModulesOptions`
--

DROP TABLE IF EXISTS `tblUsers_ModulesOptions`;
CREATE TABLE `tblUsers_ModulesOptions` (
  `User_ModuleOptionId` smallint(4) UNSIGNED NOT NULL,
  `UserId` smallint(4) UNSIGNED NOT NULL,
  `ModuleOptionId` smallint(4) UNSIGNED NOT NULL,
  `User_ModuleOptionStatusId` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Transition Users_Modules-Options table';

--
-- Volcado de datos para la tabla `tblUsers_ModulesOptions`
--

INSERT INTO `tblUsers_ModulesOptions` (`User_ModuleOptionId`, `UserId`, `ModuleOptionId`, `User_ModuleOptionStatusId`) VALUES
(1, 1, 2, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tblAreas`
--
ALTER TABLE `tblAreas`
  ADD PRIMARY KEY (`AreaId`),
  ADD KEY `INDEX_INSTITUTION_ID` (`InstitutionId`);

--
-- Indices de la tabla `tblCases`
--
ALTER TABLE `tblCases`
  ADD PRIMARY KEY (`CaseId`),
  ADD KEY `INDEX_PATIENT_ID` (`PatientId`),
  ADD KEY `INDEX_USER_ID` (`UserId`);

--
-- Indices de la tabla `tblCasesConsultations`
--
ALTER TABLE `tblCasesConsultations`
  ADD PRIMARY KEY (`CaseConsultationId`),
  ADD KEY `INDEX_CASE_ID` (`CaseId`);

--
-- Indices de la tabla `tblCasesConsultationsDx`
--
ALTER TABLE `tblCasesConsultationsDx`
  ADD PRIMARY KEY (`CaseConsultationDxId`),
  ADD KEY `INDEX_CASE_CONSULTATION_ID` (`CaseConsultationId`);

--
-- Indices de la tabla `tblCasesExplorations`
--
ALTER TABLE `tblCasesExplorations`
  ADD PRIMARY KEY (`CaseExplorationId`),
  ADD KEY `INDEX_CASE_ID` (`CaseId`);

--
-- Indices de la tabla `tblCasesExplorationsParams`
--
ALTER TABLE `tblCasesExplorationsParams`
  ADD PRIMARY KEY (`CaseExplorationParamId`),
  ADD KEY `INDEX_CASE_EXPLORATION_ID` (`CaseExplorationId`) USING BTREE;

--
-- Indices de la tabla `tblCasesLabtests`
--
ALTER TABLE `tblCasesLabtests`
  ADD PRIMARY KEY (`CaseLabtestId`),
  ADD KEY `INDEX_CASE_ID` (`CaseId`);

--
-- Indices de la tabla `tblCasesLabtestsParams`
--
ALTER TABLE `tblCasesLabtestsParams`
  ADD PRIMARY KEY (`CaseLabtestParamId`),
  ADD KEY `INDEX_CASE_LABTEST_ID` (`CaseLabtestId`) USING BTREE;

--
-- Indices de la tabla `tblCPCategories`
--
ALTER TABLE `tblCPCategories`
  ADD PRIMARY KEY (`CPCategoryId`),
  ADD UNIQUE KEY `UNIQUE_CP_CATEGORY_NAME` (`CPCategoryName`) USING BTREE;

--
-- Indices de la tabla `tblCPFields`
--
ALTER TABLE `tblCPFields`
  ADD PRIMARY KEY (`CPFieldId`),
  ADD UNIQUE KEY `UNIQUE_CP_FIELD_NAME` (`CPFieldName`) USING BTREE,
  ADD KEY `INDEX_CP_CATEGORY_ID` (`CPCategoryId`) USING BTREE;

--
-- Indices de la tabla `tblInstitutions`
--
ALTER TABLE `tblInstitutions`
  ADD PRIMARY KEY (`InstitutionId`),
  ADD UNIQUE KEY `UNIQUE_INSTITUTION_NAME` (`InstitutionName`) USING BTREE,
  ADD UNIQUE KEY `UNIQUE_INSTITUTION_SHORTNAME` (`InstitutionShortname`) USING BTREE;

--
-- Indices de la tabla `tblModules`
--
ALTER TABLE `tblModules`
  ADD PRIMARY KEY (`ModuleId`),
  ADD UNIQUE KEY `UNIQUE_MODULE_NAME` (`ModuleName`);

--
-- Indices de la tabla `tblModulesOptions`
--
ALTER TABLE `tblModulesOptions`
  ADD PRIMARY KEY (`ModuleOptionId`),
  ADD KEY `INDEX_MODULE_ID` (`ModuleId`) USING BTREE;

--
-- Indices de la tabla `tblPatients`
--
ALTER TABLE `tblPatients`
  ADD PRIMARY KEY (`PatientId`),
  ADD KEY `INDEX_PATIENT_STATUS_ID` (`PatientStatusId`) USING BTREE;

--
-- Indices de la tabla `tblPatients_CPFields`
--
ALTER TABLE `tblPatients_CPFields`
  ADD PRIMARY KEY (`Patient_CPFieldId`),
  ADD KEY `INDEX_PATIENT_ID` (`PatientId`),
  ADD KEY `INDEX_CPFIELD_ID` (`CPFieldId`);

--
-- Indices de la tabla `tblUsers`
--
ALTER TABLE `tblUsers`
  ADD PRIMARY KEY (`UserId`),
  ADD UNIQUE KEY `UNIQUE_USER_NAME` (`Username`),
  ADD UNIQUE KEY `UNIQUE_USER_EMAIL` (`Email`),
  ADD KEY `INDEX_AREA_ID` (`AreaId`);

--
-- Indices de la tabla `tblUsers_ModulesOptions`
--
ALTER TABLE `tblUsers_ModulesOptions`
  ADD PRIMARY KEY (`User_ModuleOptionId`),
  ADD KEY `INDEX_USER_ID` (`UserId`) USING BTREE,
  ADD KEY `INDEX_MODULE_OPTION_ID` (`ModuleOptionId`) USING BTREE;

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tblAreas`
--
ALTER TABLE `tblAreas`
  MODIFY `AreaId` tinyint(2) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tblCases`
--
ALTER TABLE `tblCases`
  MODIFY `CaseId` mediumint(7) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblCasesConsultations`
--
ALTER TABLE `tblCasesConsultations`
  MODIFY `CaseConsultationId` mediumint(7) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblCasesConsultationsDx`
--
ALTER TABLE `tblCasesConsultationsDx`
  MODIFY `CaseConsultationDxId` int(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblCasesExplorations`
--
ALTER TABLE `tblCasesExplorations`
  MODIFY `CaseExplorationId` mediumint(7) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblCasesExplorationsParams`
--
ALTER TABLE `tblCasesExplorationsParams`
  MODIFY `CaseExplorationParamId` int(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblCasesLabtests`
--
ALTER TABLE `tblCasesLabtests`
  MODIFY `CaseLabtestId` mediumint(7) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblCasesLabtestsParams`
--
ALTER TABLE `tblCasesLabtestsParams`
  MODIFY `CaseLabtestParamId` int(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblCPCategories`
--
ALTER TABLE `tblCPCategories`
  MODIFY `CPCategoryId` tinyint(1) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tblCPFields`
--
ALTER TABLE `tblCPFields`
  MODIFY `CPFieldId` smallint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `tblInstitutions`
--
ALTER TABLE `tblInstitutions`
  MODIFY `InstitutionId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tblModules`
--
ALTER TABLE `tblModules`
  MODIFY `ModuleId` tinyint(2) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tblModulesOptions`
--
ALTER TABLE `tblModulesOptions`
  MODIFY `ModuleOptionId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tblPatients`
--
ALTER TABLE `tblPatients`
  MODIFY `PatientId` mediumint(7) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblPatients_CPFields`
--
ALTER TABLE `tblPatients_CPFields`
  MODIFY `Patient_CPFieldId` mediumint(7) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblUsers`
--
ALTER TABLE `tblUsers`
  MODIFY `UserId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tblUsers_ModulesOptions`
--
ALTER TABLE `tblUsers_ModulesOptions`
  MODIFY `User_ModuleOptionId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tblAreas`
--
ALTER TABLE `tblAreas`
  ADD CONSTRAINT `FK_Areas__InstitutionId` FOREIGN KEY (`InstitutionId`) REFERENCES `tblInstitutions` (`InstitutionId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblCases`
--
ALTER TABLE `tblCases`
  ADD CONSTRAINT `FK_Cases__PatientId` FOREIGN KEY (`PatientId`) REFERENCES `tblPatients` (`PatientId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Cases__UserId` FOREIGN KEY (`UserId`) REFERENCES `tblUsers` (`UserId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblCasesConsultations`
--
ALTER TABLE `tblCasesConsultations`
  ADD CONSTRAINT `FK_CasesConsultations__CaseId` FOREIGN KEY (`CaseId`) REFERENCES `tblCases` (`CaseId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblCasesConsultationsDx`
--
ALTER TABLE `tblCasesConsultationsDx`
  ADD CONSTRAINT `FK_CasesConsultationsDx__CaseConsultationId` FOREIGN KEY (`CaseConsultationId`) REFERENCES `tblCasesConsultations` (`CaseConsultationId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblCasesExplorations`
--
ALTER TABLE `tblCasesExplorations`
  ADD CONSTRAINT `FK_CasesExplorations__CaseId` FOREIGN KEY (`CaseId`) REFERENCES `tblCases` (`CaseId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblCasesExplorationsParams`
--
ALTER TABLE `tblCasesExplorationsParams`
  ADD CONSTRAINT `FK_CasesExplorationsParams__CaseExplorationId` FOREIGN KEY (`CaseExplorationId`) REFERENCES `tblCasesExplorations` (`CaseExplorationId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblCasesLabtests`
--
ALTER TABLE `tblCasesLabtests`
  ADD CONSTRAINT `FK_CasesLabtests__CaseId` FOREIGN KEY (`CaseId`) REFERENCES `tblCases` (`CaseId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblCasesLabtestsParams`
--
ALTER TABLE `tblCasesLabtestsParams`
  ADD CONSTRAINT `FK_CasesLabtestsParams__CaseLabtestId` FOREIGN KEY (`CaseLabtestId`) REFERENCES `tblCasesLabtests` (`CaseLabtestId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblCPFields`
--
ALTER TABLE `tblCPFields`
  ADD CONSTRAINT `FK_CPFields__CPCategoryId` FOREIGN KEY (`CPCategoryId`) REFERENCES `tblCPCategories` (`CPCategoryId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblModulesOptions`
--
ALTER TABLE `tblModulesOptions`
  ADD CONSTRAINT `FK_ModulesOptions__ModuleId` FOREIGN KEY (`ModuleId`) REFERENCES `tblModules` (`ModuleId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblPatients_CPFields`
--
ALTER TABLE `tblPatients_CPFields`
  ADD CONSTRAINT `FK_Patients_CPFields__CPFieldId` FOREIGN KEY (`CPFieldId`) REFERENCES `tblCPFields` (`CPFieldId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Patients_CPFields__PatientId` FOREIGN KEY (`PatientId`) REFERENCES `tblPatients` (`PatientId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblUsers`
--
ALTER TABLE `tblUsers`
  ADD CONSTRAINT `FK_Users__AreaId` FOREIGN KEY (`AreaId`) REFERENCES `tblAreas` (`AreaId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblUsers_ModulesOptions`
--
ALTER TABLE `tblUsers_ModulesOptions`
  ADD CONSTRAINT `FK_Users_ModulesOptions__ModuleOptionId` FOREIGN KEY (`ModuleOptionId`) REFERENCES `tblModulesOptions` (`ModuleOptionId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Users_ModulesOptions__UserId` FOREIGN KEY (`UserId`) REFERENCES `tblUsers` (`UserId`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
