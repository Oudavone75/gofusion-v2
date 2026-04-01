<?php

namespace Database\Seeders;

use App\Models\CampaignsSeason;
use App\Models\SurvayFeedback;
use App\Models\SurvayFeedbackQuestion;
use App\Models\SurvayFeedbackQuestionOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use function Pest\Laravel\options;

class SurveyFeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaign_seasons = CampaignsSeason::with(['goSessions.goSessionSteps', 'goSessions.goSessionSteps' => function ($q) {
            $q->where('position', 6);
        }])->where('status', 'active')->get();

        $survey_feedback = [
            'title' => 'Retour d’enquête',
            'description' => 'Description du retour d’enquête',
            'questions' => [
                'À quelle fréquence utilisez-vous les transports en commun ou le covoiturage au lieu de conduire seul ?' => [
                    'Toujours',
                    'Souvent',
                    'Parfois',
                    'Jamais'
                ],
                'Essayez-vous activement de réduire votre consommation d’électricité à la maison (par exemple, éteindre les lumières, utiliser des appareils économes en énergie) ?' => [
                    'Oui, toujours',
                    'Parfois',
                    'Rarement',
                    'Pas du tout'
                ],
                'À quel point êtes-vous conscient de votre empreinte carbone personnelle ?' => [
                    'Très conscient et je la suis',
                    'Assez conscient',
                    'J’en ai entendu parler mais je ne sais pas grand-chose',
                    'Pas du tout conscient'
                ],
                'Quel type de régime alimentaire suivez-vous ?' => [
                    'Végétarien/Végétalien',
                    'Principalement à base de plantes avec un peu de viande',
                    'Équilibré, avec consommation régulière de viande',
                    'Forte consommation de viande'
                ],
                'Soutenez-vous ou participez-vous au recyclage ou au compostage ?' => [
                    'Oui, régulièrement',
                    'Occasionnellement',
                    'Rarement',
                    'Pas du tout'
                ],
                'À quelle fréquence achetez-vous des produits locaux ou écologiques ?' => [
                    'Fréquemment',
                    'Parfois',
                    'Rarement',
                    'Jamais'
                ],
                'Avez-vous déjà planté un arbre ou participé à une campagne environnementale ?' => [
                    'Oui, plusieurs fois',
                    'Une ou deux fois',
                    'J’y ai pensé mais je ne l’ai pas fait',
                    'Jamais'
                ],
                'Croyez-vous que le changement climatique est causé par l’activité humaine ?' => [
                    'Tout à fait d’accord',
                    'Plutôt d’accord',
                    'Pas sûr',
                    'Pas d’accord'
                ],
                'Réduire votre empreinte carbone est-il important pour vous personnellement ?' => [
                    'Très important',
                    'Assez important',
                    'Neutre',
                    'Pas important'
                ],
                'Seriez-vous prêt à changer vos habitudes quotidiennes pour lutter contre le changement climatique ?' => [
                    'Oui, absolument',
                    'Peut-être, si c’est pratique',
                    'Pas sûr',
                    'Non'
                ],
            ]
        ];
        foreach ($campaign_seasons as $campaign_season) {
            foreach ($campaign_season->goSessions as $go_session) {
                foreach ($go_session->goSessionSteps as $step) {
                    $survey_feedback_model = SurvayFeedback::updateOrCreate([
                        'go_session_step_id' => $step->id,
                        'title' => $survey_feedback['title'],
                        'description' => $survey_feedback['description'],
                        'points' => 20
                    ]);
                    foreach($survey_feedback['questions'] as $key => $options) {
                        $survey_feedback_question = SurvayFeedbackQuestion::updateOrCreate([
                            'survay_feedback_id' => $survey_feedback_model->id,
                            'question_text' => $key
                        ]);
                        foreach ($options as $option) {
                            SurvayFeedbackQuestionOption::updateOrCreate([
                                'question_id' => $survey_feedback_question->id,
                                'option_text' => $option
                            ]);
                        }
                    }
                }
            }
        }
    }
}
