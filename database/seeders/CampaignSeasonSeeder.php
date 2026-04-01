<?php

namespace Database\Seeders;

use App\Models\CampaignsSeason;
use App\Models\Challenge;
use App\Models\ChallengeCategory;
use App\Models\ChallengeStep;
use App\Models\Company;
use App\Models\CompanyDepartment;
use App\Models\Event;
use App\Models\EventSubmissionGuideline;
use App\Models\GoSession;
use App\Models\GoSessionStep;
use App\Models\ImageSubmissionGuideline;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\SpinWheel;
use App\Models\SurvayFeedback;
use App\Models\SurvayFeedbackQuestion;
use App\Models\SurvayFeedbackQuestionOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Theme;

class CampaignSeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DB::transaction(function () {
        $campaign = [
            'title' => 'Carbon Footprint Countdown: Act Now for a Greener Tomorrow',
            'description' => 'Excessive carbon emissions are the leading cause of climate change, affecting ecosystems, air quality, and public health. This campaign aims to educate individuals and organizations about the environmental impact of carbon emissions and promote actionable steps to reduce their carbon footprint. Through workshops, community clean-ups, tree plantation drives, and awareness content, we strive to inspire change and build a sustainable future. Join us in reducing emissions, supporting renewable energy, and advocating for climate-friendly policies. Every action counts — let’s make our planet breathe again.',
            'start_date' => '2025-07-01',
            'end_date' => '2025-09-01',
            'created_by' => 1,
            'status' => 'active',
            'reward' => 1000
        ];
        $season = [
            'title' => 'Summer Season',
            'description' => 'Excessive carbon emissions are the leading cause of climate change, affecting ecosystems, air quality, and public health. This campaign aims to educate individuals and organizations about the environmental impact of carbon emissions and promote actionable steps to reduce their carbon footprint. Through workshops, community clean-ups, tree plantation drives, and awareness content, we strive to inspire change and build a sustainable future. Join us in reducing emissions, supporting renewable energy, and advocating for climate-friendly policies. Every action counts — let’s make our planet breathe again.',
            'start_date' => '2025-06-01',
            'end_date' => '2025-09-01',
            'created_by' => 1,
            'status' => 'active',
            'reward' => 1000
        ];
        $campaign_sessions = [
            [
                'title' => 'Understanding the Carbon Crisis',
                'description' => 'Explore the science behind carbon emissions and their role in climate change. Learn about greenhouse gases and their global impact.'
            ],
            [
                'title' => 'Carbon Footprint: What’s Yours?',
                'description' => 'A hands-on session where participants calculate their own carbon footprint and discover the biggest personal and household contributors.'
            ],
            [
                'title' => 'Transportation and Emissions',
                'description' => 'Dive into the environmental cost of different transportation methods and explore sustainable commuting options.'
            ],
            [
                'title' => 'Energy Use at Home and Office',
                'description' => 'Learn practical steps to reduce energy consumption, adopt energy-efficient appliances, and transition to renewable sources.'
            ],
            [
                'title' => 'Sustainable Food Habits',
                'description' => 'Discover how your diet affects the planet, from meat consumption to food waste, and explore climate-smart food choices.'
            ],
            [
                'title' => 'The Power of Trees',
                'description' => 'Join our tree-planting drive while learning about carbon sequestration and the importance of green urban spaces.'
            ],
            [
                'title' => 'Waste Management for the Climate',
                'description' => 'Explore the link between waste and emissions, and get tips on recycling, composting, and zero-waste living.'
            ],
            [
                'title' => 'Green Business Practices',
                'description' => 'An interactive discussion for professionals on reducing emissions at the organizational level through smart policies and innovation.'
            ],
            [
                'title' => 'Climate Policy & Citizen Action',
                'description' => 'Understand international and national climate policies and how individuals can advocate for systemic change.'
            ],
            [
                'title' => 'Commit to Change: Your Carbon Pledge',
                'description' => 'A closing session where participants set personal or group carbon reduction goals and share their action plans.'
            ]
        ];

        $companies = Company::whereIn('code', ['TN001', 'GL002', 'BO003'])->get();
        $steps = [
            [
                'created_by' => 1,
                'title' => 'Step One',
                'description' => 'Step One desc',
                'position' => 1
            ],
            [
                'created_by' => 1,
                'title' => 'Step Two',
                'description' => 'Step Two desc',
                'position' => 2
            ],
            [
                'created_by' => 1,
                'title' => 'Step Three',
                'description' => 'Step Three desc',
                'position' => 3
            ],
            [
                'created_by' => 1,
                'title' => 'Step Four',
                'description' => 'Step Four desc',
                'position' => 4
            ],
            [
                'created_by' => 1,
                'title' => 'Step Five',
                'description' => 'Step Five desc',
                'position' => 5
            ],
            [
                'created_by' => 1,
                'title' => 'Step Six',
                'description' => 'Step Six desc',
                'position' => 6
            ]
        ];

        $quiz = [
            'title' => 'fist step',
            'description' => 'first step description',
            'questions' => [
                [
                    'created_by' => 1,
                    'question_text' => 'What is the largest source of human-made carbon dioxide emissions?',
                    'options' => [
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Agriculture'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 1,
                            'option_text' => 'Burning fossil fuels'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Plastic waste'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Deforestation'
                        ]
                    ]
                ],
                [
                    'created_by' => 1,
                    'question_text' => 'Which household item surprisingly increases your carbon footprint?',
                    'options' => [
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Books'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Indoor plants'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 1,
                            'option_text' => 'Clothes dryer'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Microwave'
                        ]
                    ]
                ],
                [
                    'created_by' => 1,
                    'question_text' => 'What absorbs the most carbon dioxide from the atmosphere?',
                    'options' => [
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Rainforests'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Grasslands'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Urban trees'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 1,
                            'option_text' => 'Oceans'
                        ]
                    ]
                ],
                [
                    'created_by' => 1,
                    'question_text' => 'What’s the average carbon footprint of a person per year (globally)?',
                    'options' => [
                        [
                            'created_by' => 1,
                            'is_correct' => 1,
                            'option_text' => '4 tons'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => '1 ton'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => '10 ton'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => '20 ton'
                        ]
                    ]
                ],
                [
                    'created_by' => 1,
                    'question_text' => 'Which food has the highest carbon footprint per kg produced?',
                    'options' => [
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Chicken'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Eggs'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 1,
                            'option_text' => 'Beef'
                        ],
                        [
                            'created_by' => 1,
                            'is_correct' => 0,
                            'option_text' => 'Lentils'
                        ]
                    ]
                ]
            ]
        ];

        $image_guideline = [
            'title' => 'Plant & Post: Grow the Green!',
            'description' => 'Join the movement to green the planet — one tree at a time! To complete this challenge, plant a new tree in your neighborhood, garden, or community space and upload a clear photo of your planted tree.',
            // 'created_by ' => 1,
            'guideline_type' => 'text',
            'guideline_text' => 'Plant a tree, Take a photo showing you with the newly planted tree, Upload the photo as proof of your contribution, Share your impact and inspire others to do the same!',
            'points' => 20,
            'guideline_file' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ];

        $event = [
            "title" => "Green Trail Tree Planting Day",
            "description" => "Join the community to plant native trees along the Green Trail and help restore local biodiversity. A fun, family-friendly day with workshops, games, and snacks provided!",
            "location" => "Green Trail Park, Riverbend City",
            "start_date" => "2025-08-15T10:00:00",
            "end_date" => "2025-08-15T16:00:00",
            "qr_code" => "greentrail2025",
            "event_type" => "onsite",
            "guideline" => [
                "guideline_type" => "text",
                "guideline_text" => "To complete this challenge, participants must physically attend the event, locate and scan the official QR code displayed at the venue, and upload a clear photo of the QR code as proof of attendance. Only freshly taken images of the QR code will be accepted—screenshots or unrelated photos will be rejected. This ensures genuine participation and helps verify on-site engagement. Successful submissions may earn points, badges, or rewards depending on the campaign setup.",
                "points" => 20
            ]
        ];

        $themeIds = Theme::pluck('id')->toArray(); // fetch all theme IDs from DB
        $challenge_categories = ChallengeCategory::pluck('id')->toArray();
        $challenge = [
            'theme_id' => fake()->randomElement($themeIds),
            'title' => 'Employee Wellness Challenge',
            'description' => 'Encourage employees to complete 10,000 steps daily for a week to promote health and engagement.',
            'status' => 'active',
            'points' => rand(1, 10),
            'attempted_points' => rand(1, 10),
            'is_global' => rand(0, 1),
            'image_path' => 'https://picsum.photos/600/400',
        ];

        $spin_wheel = [
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'points' => 2.5,
            'bonus_leaves' => 3,
            'promo_codes' => 'PROMO2025',
        ];

        $survey_feedback = [
            "title" => "Environmental Impact & Carbon Awareness Survey",
            "description" => "This survey aims to gather feedback on individual awareness and actions toward reducing carbon emissions and protecting the environment.",
            "questions" => [
                [
                    "question_text" => "How concerned are you about climate change?",
                    "options" => [
                        "Very Concerned",
                        "Somewhat Concerned",
                        "Not Very Concerned",
                        "Not Concerned At All"
                    ]
                ],
                [
                    "question_text" => "How often do you use public transport or carpool instead of driving alone?",
                    "options" => [
                        "Always",
                        "Often",
                        "Rarely",
                        "Never"
                    ]
                ],
                [
                    "question_text" => "How do you usually dispose of plastic waste?",
                    "options" => [
                        "Recycle it properly",
                        "Throw it with regular trash",
                        "Burn it",
                        "I don’t use plastic"
                    ]
                ],
                [
                    "question_text" => "How energy-efficient is your home?",
                    "options" => [
                        "Very Efficient (solar panels, LED lights, etc.)",
                        "Somewhat Efficient",
                        "Not Very Efficient",
                        "Not Efficient At All"
                    ]
                ],
                [
                    "question_text" => "Do you actively try to reduce your carbon footprint?",
                    "options" => [
                        "Yes, consistently",
                        "Sometimes",
                        "Rarely",
                        "Never"
                    ]
                ],
                [
                    "question_text" => "How frequently do you purchase eco-friendly products?",
                    "options" => [
                        "Very Frequently",
                        "Occasionally",
                        "Rarely",
                        "Never"
                    ]
                ],
                [
                    "question_text" => "What is your attitude toward renewable energy sources (solar, wind, etc.)?",
                    "options" => [
                        "Strongly Supportive",
                        "Supportive",
                        "Neutral",
                        "Not Supportive"
                    ]
                ],
                [
                    "question_text" => "How important is environmental education in your daily life?",
                    "options" => [
                        "Very Important",
                        "Somewhat Important",
                        "Not Very Important",
                        "Not Important At All"
                    ]
                ],
                [
                    "question_text" => "How often do you eat plant-based meals to reduce carbon emissions?",
                    "options" => [
                        "Every Day",
                        "A Few Times a Week",
                        "Rarely",
                        "Never"
                    ]
                ],
                [
                    "question_text" => "Would you support a local carbon-reduction initiative?",
                    "options" => [
                        "Absolutely",
                        "Maybe",
                        "Unlikely",
                        "Not at All"
                    ]
                ]
            ]
        ];

        DB::statement('SET foreign_key_checks = 0;');
        CampaignsSeason::truncate();
        GoSession::truncate();
        GoSessionStep::truncate();
        Quiz::truncate();
        QuizQuestion::truncate();
        QuizQuestionOption::truncate();
        ImageSubmissionGuideline::truncate();
        Event::truncate();
        EventSubmissionGuideline::truncate();
        SpinWheel::truncate();
        SurvayFeedback::truncate();
        SurvayFeedbackQuestion::truncate();
        SurvayFeedbackQuestionOption::truncate();
        ChallengeStep::truncate();

        // campaigns seeding
        foreach ($companies as $company) {
            $campaign_season = CampaignsSeason::create(
                array_merge($campaign, ['company_id' => $company->id])
            );
            foreach ($campaign_sessions as $session) {
                $go_session = GoSession::create(
                    array_merge($session, [
                        'campaign_season_id' => $campaign_season->id,
                        'created_by' => 1,
                        'status' => 'active'
                    ])
                );
                foreach ($steps as $key => $step) {
                    $step_model = GoSessionStep::create(
                        array_merge($step, [
                            'go_session_id' => $go_session->id,
                            'created_by' => 1,
                            'status' => 'active'
                        ])
                    );
                    if ($key == 0) {
                        $quiz_task = Quiz::create([
                            "go_session_step_id" => $step_model->id,
                            "created_by" => 1,
                            "title" => $quiz["title"],
                            "description" => $quiz['description'],
                            "status" => "active",
                            "points" => 10,
                            "campaign_season_id" => $campaign_season->id,
                            "company_id" => $campaign_season->company_id,
                            "go_session_id" => $go_session->id,
                            "quiz_type" => "custom"
                        ]);
                        foreach ($quiz['questions'] as $question) {
                            $quiz_question = QuizQuestion::create([
                                "quiz_id" => $quiz_task->id,
                                "created_by" => 1,
                                "question_text" => $question['question_text'],
                                "points" => 2
                            ]);
                            foreach ($question['options'] as $option) {
                                QuizQuestionOption::create(
                                    array_merge(["quiz_question_id" => $quiz_question->id], $option)
                                );
                            }
                        }
                    }
                    if ($key == 1) {
                        ImageSubmissionGuideline::create(
                            array_merge(["go_session_step_id" => $step_model->id], $image_guideline)
                        );
                    }
                    if ($key == 2) {
                        $event_model = Event::create([
                            "title" => $event['title'],
                            "description" => $event['description'],
                            "event_type" => $event['event_type'],
                            "location" => $event['location'],
                            "start_date" => $event['start_date'],
                            "end_date" => $event['end_date'],
                            "qr_code" => $event['qr_code']
                        ]);
                        EventSubmissionGuideline::create(
                            array_merge([
                                "event_id" => $event_model->id,
                                "go_session_step_id" => $step_model->id
                            ], $event['guideline'])
                        );
                    }
                    if ($key == 3) {
                        Challenge::create([
                            "go_session_step_id" => $step_model->id,
                            'status' => $challenge['status'],
                            'points' => $challenge['points'],
                        ]);
                        for ($i = 0; $i < count($themeIds); $i++) {
                            ChallengeStep::create([
                                'user_id' => $campaign_season->created_by,
                                'campaign_id' => $campaign_season->id,
                                ...$challenge, // Spread all existing challenge data
                                'theme_id' => $themeIds[$i],
                                'company_id' => $campaign_season->company_id,
                                'department_id' => $campaign_season->company_department_id,
                                'go_session_step_id' => $step_model->id,
                                'challenge_category_id' => $challenge_categories[rand(0, 1)],
                                'title' => $challenge['title'],
                                'description' => $challenge['description'],
                                'status' => $challenge['status'],
                                'points' => $challenge['points'],
                                'attempted_points' => $challenge['attempted_points'],
                                'is_global' => $challenge['is_global'],
                                'image_path' => $challenge['image_path']
                            ]);
                        }
                    }
                    if ($key == 4) {
                        SpinWheel::create([
                            'go_session_step_id' => $step_model->id,
                            'video_url' => $spin_wheel['video_url'],
                            'points' => $spin_wheel['points'],
                            'bonus_leaves' =>  $spin_wheel['bonus_leaves'],
                            'promo_codes' => $spin_wheel['promo_codes'],
                        ]);
                    }
                    if ($key == 5) {
                        $survey_feedback_model = SurvayFeedback::create([
                            'go_session_step_id' => $step_model->id,
                            'title' => $survey_feedback['title'],
                            'description' => $survey_feedback['description'],
                            'points' => 10
                        ]);
                        foreach ($survey_feedback['questions'] as $survey_feedback_question) {
                            $survey_question = SurvayFeedbackQuestion::create([
                                'survay_feedback_id' => $survey_feedback_model->id,
                                'question_text' => $survey_feedback_question['question_text']
                            ]);
                            foreach ($survey_feedback_question['options'] as $option) {
                                SurvayFeedbackQuestionOption::create([
                                    'question_id' => $survey_question->id,
                                    'option_text' => $option
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // seasons seeding
        $campaign_season = CampaignsSeason::create(
            $season
        );
        foreach ($campaign_sessions as $session) {
            $go_session = GoSession::create(
                array_merge($session, [
                    'campaign_season_id' => $campaign_season->id,
                    'created_by' => 1,
                    'status' => 'active'
                ])
            );
            foreach ($steps as $key => $step) {
                $step_model = GoSessionStep::create(
                    array_merge($step, [
                        'go_session_id' => $go_session->id,
                        'created_by' => 1,
                        'status' => 'active'
                    ])
                );
                if ($key == 0) {
                    $quiz_task = Quiz::create([
                        "go_session_step_id" => $step_model->id,
                        "created_by" => 1,
                        "title" => $quiz["title"],
                        "description" => $quiz['description'],
                        "status" => "active",
                        "points" => 10,
                        "campaign_season_id" => $campaign_season->id,
                        "company_id" => $campaign_season->company_id,
                        "go_session_id" => $go_session->id,
                        "quiz_type" => "custom"
                    ]);
                    foreach ($quiz['questions'] as $question) {
                        $quiz_question = QuizQuestion::create([
                            "quiz_id" => $quiz_task->id,
                            "created_by" => 1,
                            "question_text" => $question['question_text'],
                            "points" => 2
                        ]);
                        foreach ($question['options'] as $option) {
                            QuizQuestionOption::create(
                                array_merge(["quiz_question_id" => $quiz_question->id], $option)
                            );
                        }
                    }
                }
                if ($key == 1) {
                    ImageSubmissionGuideline::create(
                        array_merge(["go_session_step_id" => $step_model->id], $image_guideline)
                    );
                }
                if ($key == 2) {
                    $event_model = Event::create([
                        "title" => $event['title'],
                        "description" => $event['description'],
                        "event_type" => $event['event_type'],
                        "location" => $event['location'],
                        "start_date" => $event['start_date'],
                        "end_date" => $event['end_date'],
                        "qr_code" => $event['qr_code']
                    ]);
                    EventSubmissionGuideline::create(
                        array_merge([
                            "event_id" => $event_model->id,
                            "go_session_step_id" => $step_model->id
                        ], $event['guideline'])
                    );
                }
                if ($key == 3) {
                    Challenge::create([
                        "go_session_step_id" => $step_model->id,
                        'status' => $challenge['status'],
                        'points' => $challenge['points'],
                    ]);
                    for ($i = 0; $i < count($themeIds); $i++) {
                        ChallengeStep::create([
                            'user_id' => $campaign_season->created_by,
                            'campaign_id' => $campaign_season->id,
                            ...$challenge, // Spread all existing challenge data
                            'theme_id' => $themeIds[$i],
                            'company_id' => $campaign_season->company_id,
                            'department_id' => $campaign_season->company_department_id,
                            'go_session_step_id' => $step_model->id,
                            'challenge_category_id' => $challenge_categories[rand(0, 1)],
                            'title' => $challenge['title'],
                            'description' => $challenge['description'],
                            'status' => $challenge['status'],
                            'points' => $challenge['points'],
                            'attempted_points' => $challenge['attempted_points'],
                            'is_global' => $challenge['is_global'],
                            'image_path' => $challenge['image_path']
                        ]);
                    }
                }
                if ($key == 4) {
                    SpinWheel::create([
                        'go_session_step_id' => $step_model->id,
                        'video_url' => $spin_wheel['video_url'],
                        'points' => $spin_wheel['points'],
                        'bonus_leaves' =>  $spin_wheel['bonus_leaves'],
                        'promo_codes' => $spin_wheel['promo_codes'],
                    ]);
                }
                if ($key == 5) {
                    $survey_feedback_model = SurvayFeedback::create([
                        'go_session_step_id' => $step_model->id,
                        'title' => $survey_feedback['title'],
                        'description' => $survey_feedback['description'],
                        'points' => 10
                    ]);
                    foreach ($survey_feedback['questions'] as $survey_feedback_question) {
                        $survey_question = SurvayFeedbackQuestion::create([
                            'survay_feedback_id' => $survey_feedback_model->id,
                            'question_text' => $survey_feedback_question['question_text']
                        ]);
                        foreach ($survey_feedback_question['options'] as $option) {
                            SurvayFeedbackQuestionOption::create([
                                'question_id' => $survey_question->id,
                                'option_text' => $option
                            ]);
                        }
                    }
                }
            }
        }
        DB::statement('SET foreign_key_checks = 1;');
        // });
    }
}
