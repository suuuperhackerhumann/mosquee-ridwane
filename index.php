<?php
require_once __DIR__ . '/config/config.php';

$page_titre = 'Accueil';
$page_description = 'Découvrez la Mosquée Ridwane à Riviera Abatta, Abidjan : horaires de prière, actualités, événements, enseignements coraniques et services à la communauté.';
$page_meta_title = 'Mosquée Ridwane — Riviera Abatta, Abidjan';
$page_og_description = 'Horaires des prières, actualités et vie de la communauté de la Mosquée Ridwane à Abidjan.';
$horaires = getHorairesDuJour();

$stmt = $pdo->query("SELECT a.*, c.nom AS categorie FROM actualites a LEFT JOIN categories c ON a.categorie_id = c.id WHERE a.statut='publie' ORDER BY a.date_publication DESC LIMIT 3");
$dernieres_actus = $stmt->fetchAll();

$stmt = $pdo->query("SELECT e.*, c.nom AS categorie FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id WHERE e.statut='publie' AND e.date_evenement >= CURDATE() ORDER BY e.date_evenement ASC LIMIT 1");
$prochain_evenement = $stmt->fetch();

$versets = [
    ['arabe' => 'الَّذِينَ آمَنُوا وَتَطْمَئِنُّ قُلُوبُهُم بِذِكْرِ اللَّهِ ۗ أَلَا بِذِكْرِ اللَّهِ تَطْمَئِنُّ الْقُلُوبُ', 'traduction' => 'Ceux qui croient trouvent la paix dans le rappel d’Allah. C’est bien par le rappel d’Allah que les cœurs trouvent la sérénité.', 'reference' => 'Sourate Ar-Ra‘d, 13:28'],
    ['arabe' => 'فَإِنَّ مَعَ الْعُسْرِ يُسْرًا ۝ إِنَّ مَعَ الْعُسْرِ يُسْرًا', 'traduction' => 'Avec la difficulté vient certes la facilité. Oui, avec la difficulté vient la facilité.', 'reference' => 'Sourate Ash-Sharh, 94:5-6'],
    ['arabe' => 'فَتَعَالَى اللَّهُ الْمَلِكُ الْحَقُّ ۗ وَلَا تَعْجَلْ بِالْقُرْآنِ مِن قَبْلِ أَن يُقْضَىٰ إِلَيْكَ وَحْيُهُ ۖ وَقُل رَّبِّ زِدْنِي عِلْمًا', 'traduction' => 'Allah, le vrai Souverain, est exalté. Ne te hâte pas de réciter le Coran avant que sa révélation ne soit achevée, et dis : « Seigneur, accrois mon savoir. »', 'reference' => 'Sourate Ta-Ha, 20:114'],
    ['arabe' => 'فَاذْكُرُونِي أَذْكُرْكُمْ وَاشْكُرُوا لِي وَلَا تَكْفُرُونِ', 'traduction' => 'Souvenez-vous de Moi, Je Me souviendrai de vous. Soyez reconnaissants envers Moi et ne soyez pas ingrats.', 'reference' => 'Sourate Al-Baqara, 2:152'],
    ['arabe' => 'يَا أَيُّهَا الَّذِينَ آمَنُوا اسْتَعِينُوا بِالصَّبْرِ وَالصَّلَاةِ ۚ إِنَّ اللَّهَ مَعَ الصَّابِرِينَ', 'traduction' => 'Ô vous qui croyez, cherchez secours dans la patience et la prière. Allah est avec ceux qui patientent.', 'reference' => 'Sourate Al-Baqara, 2:153'],
    ['arabe' => 'قَالَ هَلْ آمَنُكُمْ عَلَيْهِ إِلَّا كَمَا أَمِنتُكُمْ عَلَىٰ أَخِيهِ مِن قَبْلُ ۖ فَاللَّهُ خَيْرٌ حَافِظًا ۖ وَهُوَ أَرْحَمُ الرَّاحِمِينَ', 'traduction' => 'Il dit : « Vous le confierais-je comme je vous ai confié son frère auparavant ? Allah est le meilleur gardien et le plus miséricordieux. »', 'reference' => 'Sourate Yusuf, 12:64'],
    ['arabe' => 'وَيَرْزُقْهُ مِنْ حَيْثُ لَا يَحْتَسِبُ ۚ وَمَن يَتَوَكَّلْ عَلَى اللَّهِ فَهُوَ حَسْبُهُ ۚ إِنَّ اللَّهَ بَالِغُ أَمْرِهِ ۚ قَدْ جَعَلَ اللَّهُ لِكُلِّ شَيْءٍ قَدْرًا', 'traduction' => 'Allah lui accordera Ses dons par des voies inattendues. À celui qui place sa confiance en Lui, Allah suffit. Il accomplit toujours Son dessein et a fixé une mesure à toute chose.', 'reference' => 'Sourate At-Talaq, 65:3'],
    ['arabe' => 'وَإِذَا سَأَلَكَ عِبَادِي عَنِّي فَإِنِّي قَرِيبٌ ۖ أُجِيبُ دَعْوَةَ الدَّاعِ إِذَا دَعَانِ ۖ فَلْيَسْتَجِيبُوا لِي وَلْيُؤْمِنُوا بِي لَعَلَّهُمْ يَرْشُدُونَ', 'traduction' => 'Quand Mes serviteurs t’interrogent à Mon sujet, Je suis proche et Je réponds à l’appel de celui qui M’invoque. Qu’ils répondent à Mon appel et croient en Moi afin d’être bien guidés.', 'reference' => 'Sourate Al-Baqara, 2:186'],
    ['arabe' => 'وَلَا تَهِنُوا وَلَا تَحْزَنُوا وَأَنتُمُ الْأَعْلَوْنَ إِن كُنتُم مُّؤْمِنِينَ', 'traduction' => 'Ne faiblissez pas et ne vous affligez pas : vous aurez le dessus si vous êtes croyants.', 'reference' => 'Sourate Al-Imran, 3:139'],
    ['arabe' => 'فَبِمَا رَحْمَةٍ مِّنَ اللَّهِ لِنتَ لَهُمْ ۖ وَلَوْ كُنتَ فَظًّا غَلِيظَ الْقَلْبِ لَانفَضُّوا مِنْ حَوْلِكَ ۖ فَاعْفُ عَنْهُمْ وَاسْتَغْفِرْ لَهُمْ وَشَاوِرْهُمْ فِي الْأَمْرِ ۖ فَإِذَا عَزَمْتَ فَتَوَكَّلْ عَلَى اللَّهِ ۚ إِنَّ اللَّهَ يُحِبُّ الْمُتَوَكِّلِينَ', 'traduction' => 'C’est par une miséricorde d’Allah que tu as été doux envers eux. Pardonne-leur, demande pardon pour eux et consulte-les. Puis, une fois ta décision prise, place ta confiance en Allah : Il aime ceux qui Lui font confiance.', 'reference' => 'Sourate Al-Imran, 3:159'],
    ['arabe' => 'رَبَّنَا لَا تُزِغْ قُلُوبَنَا بَعْدَ إِذْ هَدَيْتَنَا وَهَبْ لَنَا مِن لَّدُنكَ رَحْمَةً ۚ إِنَّكَ أَنتَ الْوَهَّابُ', 'traduction' => 'Seigneur, ne détourne pas nos cœurs après nous avoir guidés et accorde-nous une miséricorde venant de Toi. Tu es Celui qui donne sans compter.', 'reference' => 'Sourate Al-Imran, 3:8'],
    ['arabe' => 'وَأَطِيعُوا اللَّهَ وَرَسُولَهُ وَلَا تَنَازَعُوا فَتَفْشَلُوا وَتَذْهَبَ رِيحُكُمْ ۖ وَاصْبِرُوا ۚ إِنَّ اللَّهَ مَعَ الصَّابِرِينَ', 'traduction' => 'Obéissez à Allah et à Son Messager, ne vous disputez pas au risque de perdre courage et force, et soyez patients. Allah est avec ceux qui patientent.', 'reference' => 'Sourate Al-Anfal, 8:46'],
    ['arabe' => 'قُل لَّن يُصِيبَنَا إِلَّا مَا كَتَبَ اللَّهُ لَنَا هُوَ مَوْلَانَا ۚ وَعَلَى اللَّهِ فَلْيَتَوَكَّلِ الْمُؤْمِنُونَ', 'traduction' => 'Dis : rien ne nous atteindra en dehors de ce qu’Allah a décrété pour nous. Il est notre Protecteur. Que les croyants placent leur confiance en Lui.', 'reference' => 'Sourate At-Tawba, 9:51'],
    ['arabe' => 'لَئِن شَكَرْتُمْ لَأَزِيدَنَّكُمْ ۖ وَلَئِن كَفَرْتُمْ إِنَّ عَذَابِي لَشَدِيدٌ', 'traduction' => 'Si vous êtes reconnaissants, J’augmenterai Mes bienfaits pour vous. Mais si vous êtes ingrats, Mon châtiment est sévère.', 'reference' => 'Sourate Ibrahim, 14:7'],
    ['arabe' => 'إِنَّ اللَّهَ يَأْمُرُ بِالْعَدْلِ وَالْإِحْسَانِ وَإِيتَاءِ ذِي الْقُرْبَىٰ وَيَنْهَىٰ عَنِ الْفَحْشَاءِ وَالْمُنكَرِ وَالْبَغْيِ ۚ يَعِظُكُمْ لَعَلَّكُمْ تَذَكَّرُونَ', 'traduction' => 'Allah commande la justice, la bienfaisance et la générosité envers les proches ; Il interdit la turpitude, le blâmable et l’injustice afin que vous vous rappeliez.', 'reference' => 'Sourate An-Nahl, 16:90'],
    ['arabe' => 'مَنْ عَمِلَ صَالِحًا مِّن ذَكَرٍ أَوْ أُنثَىٰ وَهُوَ مُؤْمِنٌ فَلَنُحْيِيَنَّهُ حَيَاةً طَيِّبَةً ۖ وَلَنَجْزِيَنَّهُمْ أَجْرَهُم بِأَحْسَنِ مَا كَانُوا يَعْمَلُونَ', 'traduction' => 'À tout croyant ou croyante qui accomplit de bonnes œuvres, Nous accorderons une vie heureuse et une récompense à la mesure de ses meilleures actions.', 'reference' => 'Sourate An-Nahl, 16:97'],
    ['arabe' => 'وَلَقَدْ كَرَّمْنَا بَنِي آدَمَ وَحَمَلْنَاهُمْ فِي الْبَرِّ وَالْبَحْرِ وَرَزَقْنَاهُم مِّنَ الطَّيِّبَاتِ وَفَضَّلْنَاهُمْ عَلَىٰ كَثِيرٍ مِّمَّنْ خَلَقْنَا تَفْضِيلًا', 'traduction' => 'Nous avons honoré les enfants d’Adam, les avons transportés sur terre et sur mer, leur avons accordé de bonnes choses et les avons favorisés par rapport à beaucoup de Nos créatures.', 'reference' => 'Sourate Al-Isra, 17:70'],
    ['arabe' => 'قَالَ لَا تَخَافَا إِنَّنِي مَعَكُمَا أَسْمَعُ وَأَرَىٰ', 'traduction' => 'Il dit : « Ne craignez rien. Je suis avec vous, J’entends et Je vois. »', 'reference' => 'Sourate Ta-Ha, 20:46'],
    ['arabe' => 'وَعِبَادُ الرَّحْمَٰنِ الَّذِينَ يَمْشُونَ عَلَى الْأَرْضِ هَوْنًا وَإِذَا خَاطَبَهُمُ الْجَاهِلُونَ قَالُوا سَلَامًا', 'traduction' => 'Les serviteurs du Tout Miséricordieux marchent humblement sur terre et répondent avec paix aux paroles des ignorants.', 'reference' => 'Sourate Al-Furqan, 25:63'],
    ['arabe' => 'فَسَقَىٰ لَهُمَا ثُمَّ تَوَلَّىٰ إِلَى الظِّلِّ فَقَالَ رَبِّ إِنِّي لِمَا أَنزَلْتَ إِلَيَّ مِنْ خَيْرٍ فَقِيرٌ', 'traduction' => 'Il abreuva leurs troupeaux, puis se retira à l’ombre et dit : « Seigneur, j’ai grand besoin de tout bien que Tu voudras m’accorder. »', 'reference' => 'Sourate Al-Qasas, 28:24'],
    ['arabe' => 'وَالَّذِينَ جَاهَدُوا فِينَا لَنَهْدِيَنَّهُمْ سُبُلَنَا ۚ وَإِنَّ اللَّهَ لَمَعَ الْمُحْسِنِينَ', 'traduction' => 'Ceux qui s’efforcent pour Notre cause, Nous les guiderons sur Nos chemins. Allah est avec ceux qui font le bien.', 'reference' => 'Sourate Al-Ankabut, 29:69'],
    ['arabe' => 'يَا أَيُّهَا الَّذِينَ آمَنُوا اتَّقُوا اللَّهَ وَقُولُوا قَوْلًا سَدِيدًا', 'traduction' => 'Ô vous qui croyez, craignez Allah et tenez des paroles justes.', 'reference' => 'Sourate Al-Ahzab, 33:70'],
    ['arabe' => 'قُلْ يَا عِبَادِيَ الَّذِينَ أَسْرَفُوا عَلَىٰ أَنفُسِهِمْ لَا تَقْنَطُوا مِن رَّحْمَةِ اللَّهِ ۚ إِنَّ اللَّهَ يَغْفِرُ الذُّنُوبَ جَمِيعًا ۚ إِنَّهُ هُوَ الْغَفُورُ الرَّحِيمُ', 'traduction' => 'Dis : « Ô Mes serviteurs qui avez commis des excès envers vous-mêmes, ne désespérez pas de la miséricorde d’Allah. Il pardonne tous les péchés ; Il est le Pardonneur, le Très Miséricordieux. »', 'reference' => 'Sourate Az-Zumar, 39:53'],
    ['arabe' => 'وَلَا تَسْتَوِي الْحَسَنَةُ وَلَا السَّيِّئَةُ ۚ ادْفَعْ بِالَّتِي هِيَ أَحْسَنُ فَإِذَا الَّذِي بَيْنَكَ وَبَيْنَهُ عَدَاوَةٌ كَأَنَّهُ وَلِيٌّ حَمِيمٌ', 'traduction' => 'Le bien et le mal ne sont pas égaux. Repousse le mal par ce qui est meilleur : celui qui était ton ennemi deviendra alors comme un ami chaleureux.', 'reference' => 'Sourate Fussilat, 41:34'],
    ['arabe' => 'وَلَمَن صَبَرَ وَغَفَرَ إِنَّ ذَٰلِكَ لَمِنْ عَزْمِ الْأُمُورِ', 'traduction' => 'Celui qui se montre patient et pardonne fait preuve d’une grande résolution.', 'reference' => 'Sourate Ash-Shura, 42:43'],
    ['arabe' => 'يَا أَيُّهَا النَّاسُ إِنَّا خَلَقْنَاكُم مِّن ذَكَرٍ وَأُنثَىٰ وَجَعَلْنَاكُمْ شُعُوبًا وَقَبَائِلَ لِتَعَارَفُوا ۚ إِنَّ أَكْرَمَكُمْ عِندَ اللَّهِ أَتْقَاكُمْ ۚ إِنَّ اللَّهَ عَلِيمٌ خَبِيرٌ', 'traduction' => 'Ô vous les humains, Nous vous avons créés d’un homme et d’une femme et faits peuples et tribus afin que vous vous connaissiez. Le plus noble auprès d’Allah est le plus pieux. Allah sait tout et connaît toute chose.', 'reference' => 'Sourate Al-Hujurat, 49:13'],
    ['arabe' => 'وَلَقَدْ خَلَقْنَا الْإِنسَانَ وَنَعْلَمُ مَا تُوَسْوِسُ بِهِ نَفْسُهُ ۖ وَنَحْنُ أَقْرَبُ إِلَيْهِ مِنْ حَبْلِ الْوَرِيدِ', 'traduction' => 'Nous avons créé l’être humain et savons ce que son âme lui murmure. Nous sommes plus près de lui que sa veine jugulaire.', 'reference' => 'Sourate Qaf, 50:16'],
    ['arabe' => 'وَأَن لَّيْسَ لِلْإِنسَانِ إِلَّا مَا سَعَىٰ', 'traduction' => 'L’être humain ne récoltera que le fruit de ses efforts.', 'reference' => 'Sourate An-Najm, 53:39'],
    ['arabe' => 'هُوَ الَّذِي خَلَقَ السَّمَاوَاتِ وَالْأَرْضَ فِي سِتَّةِ أَيَّامٍ ثُمَّ اسْتَوَىٰ عَلَى الْعَرْشِ ۚ يَعْلَمُ مَا يَلِجُ فِي الْأَرْضِ وَمَا يَخْرُجُ مِنْهَا وَمَا يَنزِلُ مِنَ السَّمَاءِ وَمَا يَعْرُجُ فِيهَا ۖ وَهُوَ مَعَكُمْ أَيْنَ مَا كُنتُمْ ۚ وَاللَّهُ بِمَا تَعْمَلُونَ بَصِيرٌ ۚ لَهُ مُلْكُ السَّمَاوَاتِ وَالْأَرْضِ ۚ وَإِلَى اللَّهِ تُرْجَعُ الْأُمُورُ', 'traduction' => 'C’est Lui qui a créé les cieux et la terre en six jours, puis S’est établi sur le Trône. Il sait ce qui entre dans la terre et en sort, ce qui descend du ciel et y remonte. Il est avec vous où que vous soyez. Le royaume des cieux et de la terre Lui appartient, et tout retourne à Allah.', 'reference' => 'Sourate Al-Hadid, 57:4'],
    ['arabe' => 'فَإِذَا بَلَغْنَ أَجَلَهُنَّ فَأَمْسِكُوهُنَّ بِمَعْرُوفٍ أَوْ فَارِقُوهُنَّ بِمَعْرُوفٍ ۚ وَأَشْهِدُوا ذَوَيْ عَدْلٍ مِّنكُمْ وَأَقِيمُوا الشَّهَادَةَ لِلَّهِ ۚ ذَٰلِكُمْ يُوعَظُ بِهِ مَن كَانَ يُؤْمِنُ بِاللَّهِ وَالْيَوْمِ الْآخِرِ ۚ وَمَن يَتَّقِ اللَّهَ يَجْعَل لَّهُ مَخْرَجًا', 'traduction' => 'À l’issue du délai, gardez-les avec bonté ou séparez-vous d’elles avec bonté, et témoignez avec équité. Voilà un conseil pour qui croit en Allah et au Jour dernier. À celui qui Le craint, Allah ménage une issue.', 'reference' => 'Sourate At-Talaq, 65:2'],
    ['arabe' => 'الَّذِي خَلَقَ الْمَوْتَ وَالْحَيَاةَ لِيَبْلُوَكُمْ أَيُّكُمْ أَحْسَنُ عَمَلًا ۚ وَهُوَ الْعَزِيزُ الْغَفُورُ', 'traduction' => 'Celui qui a créé la mort et la vie afin de vous éprouver et de distinguer ceux qui agissent le mieux. Il est le Tout-Puissant, le Pardonneur.', 'reference' => 'Sourate Al-Mulk, 67:2'],
    ['arabe' => 'وَاذْكُرِ اسْمَ رَبِّكَ وَتَبَتَّلْ إِلَيْهِ تَبْتِيلًا', 'traduction' => 'Évoque le nom de ton Seigneur et consacre-toi entièrement à Lui.', 'reference' => 'Sourate Al-Muzzammil, 73:8'],
    ['arabe' => 'قَدْ أَفْلَحَ مَن تَزَكَّىٰ ۝ وَذَكَرَ اسْمَ رَبِّهِ فَصَلَّىٰ', 'traduction' => 'A réussi celui qui se purifie, évoque le nom de son Seigneur et prie.', 'reference' => 'Sourate Al-Ala, 87:14-15'],
    ['arabe' => 'قَدْ أَفْلَحَ مَن زَكَّاهَا', 'traduction' => 'A réussi celui qui purifie son âme.', 'reference' => 'Sourate Ash-Shams, 91:9'],
    ['arabe' => 'وَلَسَوْفَ يُعْطِيكَ رَبُّكَ فَتَرْضَىٰ', 'traduction' => 'Ton Seigneur t’accordera bientôt Ses bienfaits, et tu en seras satisfait.', 'reference' => 'Sourate Ad-Duha, 93:5'],
    ['arabe' => 'فَإِذَا فَرَغْتَ فَانصَبْ ۝ وَإِلَىٰ رَبِّكَ فَارْغَب', 'traduction' => 'Quand tu as achevé une tâche, consacre-toi à une autre et tourne tes aspirations vers ton Seigneur.', 'reference' => 'Sourate Ash-Sharh, 94:7-8'],
    ['arabe' => 'فَمَن يَعْمَلْ مِثْقَالَ ذَرَّةٍ خَيْرًا يَرَهُ ۝ وَمَن يَعْمَلْ مِثْقَالَ ذَرَّةٍ شَرًّا يَرَهُ', 'traduction' => 'Quiconque aura fait le poids d’un atome de bien le verra, et quiconque aura fait le poids d’un atome de mal le verra.', 'reference' => 'Sourate Az-Zalzala, 99:7-8'],
    ['arabe' => 'وَالْعَصْرِ ۝ إِنَّ الْإِنسَانَ لَفِي خُسْرٍ ۝ إِلَّا الَّذِينَ آمَنُوا وَعَمِلُوا الصَّالِحَاتِ وَتَوَاصَوْا بِالْحَقِّ وَتَوَاصَوْا بِالصَّبْرِ', 'traduction' => 'Par le temps ! L’être humain est en perdition, sauf ceux qui croient, accomplissent de bonnes œuvres, s’encouragent à la vérité et s’encouragent à la patience.', 'reference' => 'Sourate Al-Asr, 103:1-3'],
    ['arabe' => 'قُلْ هُوَ اللَّهُ أَحَدٌ ۝ اللَّهُ الصَّمَدُ ۝ لَمْ يَلِدْ وَلَمْ يُولَدْ ۝ وَلَمْ يَكُن لَّهُ كُفُوًا أَحَدٌ', 'traduction' => 'Dis : « Il est Allah, l’Unique, Celui dont tout dépend. Il n’a pas engendré et n’a pas été engendré, et nul n’est égal à Lui. »', 'reference' => 'Sourate Al-Ikhlas, 112:1-4'],
    ['arabe' => 'الَّذِينَ يُنفِقُونَ فِي السَّرَّاءِ وَالضَّرَّاءِ وَالْكَاظِمِينَ الْغَيْظَ وَالْعَافِينَ عَنِ النَّاسِ ۗ وَاللَّهُ يُحِبُّ الْمُحْسِنِينَ', 'traduction' => 'Ceux qui donnent dans l’aisance comme dans l’adversité, maîtrisent leur colère et pardonnent aux gens : Allah aime ceux qui font le bien.', 'reference' => 'Sourate Al-Imran, 3:134'],
];
$aujourdhui = new DateTimeImmutable('today', new DateTimeZone('Africa/Abidjan'));
$jourIndex = intdiv((int) $aujourdhui->format('U'), 86400);
$versetDuJour = $versets[($jourIndex * 17 + 3) % count($versets)];

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="conteneur">
        <div class="hero-accueil-grille">
            <div class="hero-contenu">
                <span class="eyebrow">As-salamu alaykum</span>
                <h1 id="test"><?= h(contenuTitre('accueil_bienvenue', 'Bienvenue à la Mosquée Ridwane')) ?></h1>
                <p class="intro"><?= h(contenuTexte('accueil_presentation')) ?></p>
                <div class="hero-actions">
                    <a href="<?= BASE_URL ?>/horaires.php" class="bouton bouton-dore">Voir les horaires de prière</a>
                    <a href="<?= BASE_URL ?>/a-propos.php" class="bouton bouton-contour">Découvrir la mosquée</a>
                    <a href="https://www.hadj-ci.org/" class="bouton bouton-contour" target="_blank" rel="noopener noreferrer">Commissariat du Hadj <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                    <a href="https://www.cosim-ci.org/" class="bouton bouton-contour" target="_blank" rel="noopener noreferrer">COSIM <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                </div>
            </div>

            <aside class="verset-du-jour" aria-labelledby="titre-verset-du-jour">
                <span class="verset-etiquette" id="titre-verset-du-jour">Verset du jour</span>
                <p class="verset-arabe" lang="ar" dir="rtl"><?= h($versetDuJour['arabe']) ?></p>
                <p class="verset-traduction"><span>Traduction du sens :</span> <?= h($versetDuJour['traduction']) ?></p>
                <span class="verset-reference"><?= h($versetDuJour['reference']) ?></span>
            </aside>
        </div>

        <?php if ($horaires): ?>
        <div class="hero-horaires">
            <div class="hero-horaires-titre">
                <span>Horaires du jour</span>
                <span><?= h(formatDateFr(time())) ?></span>
            </div>
            <div class="grille-horaires-hero">
                <div data-heure-priere="<?= formatHeure($horaires['fajr']) ?>">
                    <div class="nom">Fajr</div>
                    <div class="heure"><?= formatHeure($horaires['fajr']) ?></div>
                </div>
                <div data-heure-priere="<?= formatHeure($horaires['dhuhr']) ?>">
                    <div class="nom">Dhuhr</div>
                    <div class="heure"><?= formatHeure($horaires['dhuhr']) ?></div>
                </div>
                <div data-heure-priere="<?= formatHeure($horaires['asr']) ?>">
                    <div class="nom">Asr</div>
                    <div class="heure"><?= formatHeure($horaires['asr']) ?></div>
                </div>
                <div data-heure-priere="<?= formatHeure($horaires['maghrib']) ?>">
                    <div class="nom">Maghrib</div>
                    <div class="heure"><?= formatHeure($horaires['maghrib']) ?></div>
                </div>
                <div data-heure-priere="<?= formatHeure($horaires['isha']) ?>">
                    <div class="nom">Isha</div>
                    <div class="heure"><?= formatHeure($horaires['isha']) ?></div>
                </div>
                <div data-heure-priere="<?= formatHeure($horaires['jumuah'] ?? null) ?>">
                    <div class="nom">Jumu'ah</div>
                    <div class="heure"><?= formatHeure($horaires['jumuah'] ?? null) ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Présentation courte -->
<section class="section">
    <div class="conteneur grille grille-2" style="align-items:center; gap:56px;">
        <div>
            <span class="eyebrow">Notre mosquée</span>
            <h2><?= h(contenuTitre('apropos_mission', 'Un lieu de foi, de savoir et de fraternité')) ?></h2>
            <p><?= h(extraitTexte(contenuTexte('apropos_mission', "La Mosquée Ridwane accueille chaque jour les fidèles pour les prières, l'enseignement et la vie communautaire, dans un esprit d'ouverture et de service."), 320)) ?></p>
            <a href="<?= BASE_URL ?>/a-propos.php" class="carte-lien">En savoir plus sur la mosquée →</a>
        </div>
        <?php if ($prochain_evenement): ?>
        <div class="carte">
            <div class="carte-corps">
                <div class="carte-meta">
                    <span class="badge">Prochain événement</span>
                </div>
                <h3><?= h($prochain_evenement['titre']) ?></h3>
                <p class="resume"><?= h(extraitTexte($prochain_evenement['description'], 140)) ?></p>
                <p class="resume" style="font-family:var(--police-donnee); font-weight:600; color:var(--emeraude-700);">
                    <span class="meta-icone"><i class="fa-solid fa-calendar-days icone" aria-hidden="true"></i> <?= h(formatDateFr($prochain_evenement['date_evenement'])) ?></span>
                    <?php if ($prochain_evenement['heure_evenement']): ?> — <?= formatHeure($prochain_evenement['heure_evenement']) ?><?php endif; ?>
                    <?php if ($prochain_evenement['lieu']): ?><br><span class="meta-icone"><i class="fa-solid fa-location-dot icone" aria-hidden="true"></i> <?= h($prochain_evenement['lieu']) ?></span><?php endif; ?>
                </p>
                <a href="<?= BASE_URL ?>/evenement.php?slug=<?= urlencode($prochain_evenement['slug']) ?>" class="carte-lien">Voir les détails →</a>
            </div>
        </div>
        <?php else: ?>
        <div class="carte"><div class="carte-corps">
            <h3>Aucun événement à venir pour le moment</h3>
            <p class="resume">Consultez régulièrement cette page pour connaître nos prochaines activités.</p>
        </div></div>
        <?php endif; ?>
    </div>
</section>

<!-- Actualités -->
<section class="section section-alt">
    <div class="conteneur">
        <div class="section-tete">
            <span class="eyebrow">Vie de la mosquée</span>
            <h2>Dernières actualités</h2>
        </div>
        <?php if ($dernieres_actus): ?>
        <div class="grille grille-3">
            <?php foreach ($dernieres_actus as $actu): ?>
            <a class="carte" href="<?= BASE_URL ?>/actualite.php?slug=<?= urlencode($actu['slug']) ?>">
                <div class="carte-image" style="background-image:url('<?= h(siteAssetUrl($actu['image'] ?: '/assets/img/placeholder-actu.jpg')) ?>');"></div>
                <div class="carte-corps">
                    <div class="carte-meta">
                        <?php if ($actu['categorie']): ?><span class="badge"><?= h($actu['categorie']) ?></span><?php endif; ?>
                        <span><?= h(formatDateFr($actu['date_publication'])) ?></span>
                    </div>
                    <h3><?= h($actu['titre']) ?></h3>
                    <p class="resume"><?= h($actu['resume'] ?: extraitTexte($actu['contenu'], 110)) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p>Aucune actualité publiée pour le moment.</p>
        <?php endif; ?>
        <p style="margin-top:28px;"><a href="<?= BASE_URL ?>/actualites.php" class="carte-lien">Voir toutes les actualités →</a></p>
    </div>
</section>

<!-- Enseignements -->
<section class="section">
    <div class="conteneur grille grille-2" style="align-items:center; gap:56px;">
        <div>
            <span class="eyebrow">Apprendre</span>
            <h2>Nos enseignements</h2>
            <p>École coranique, cours de Coran, d'arabe et enseignements religieux pour enfants comme pour adultes.</p>
            <a href="<?= BASE_URL ?>/enseignements.php" class="bouton bouton-emeraude">Découvrir les cours</a>
        </div>
        <div>
            <span class="eyebrow">Accompagner</span>
            <h2>Nos services</h2>
            <p>Nikah, Janazah, Zakat, consultations religieuses : la mosquée est à votre service dans les grandes étapes de la vie.</p>
            <a href="<?= BASE_URL ?>/services.php" class="bouton bouton-emeraude">Voir les services</a>
        </div>
    </div>
</section>

<!-- Dons -->
<section class="section section-emeraude">
    <div class="conteneur" style="text-align:center; max-width:640px; margin:0 auto;">
        <span class="eyebrow" style="justify-content:center;">Soutenir la mosquée</span>
        <h2><?= h(contenuTitre('dons_intro', 'Votre don fait vivre la mosquée')) ?></h2>
        <p style="color:var(--plâtre-100);"><?= h(contenuTexte('dons_intro')) ?></p>
        <a href="<?= BASE_URL ?>/dons.php" class="bouton bouton-dore">Faire un don</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>