<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Goal;
use App\Models\Milestone;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class GoalifySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a user
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create two goals
        $goals = [
            [
                'title' => 'Learn Web Development',
                'description' => 'Master full-stack web development including frontend and backend technologies',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(6),
                'status' => 'in_progress',
                'priority' => 'high',
                'progress_percentage' => 0,
            ],
            [
                'title' => 'Get Fit and Healthy',
                'description' => 'Achieve better physical health through regular exercise and proper nutrition',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(3),
                'status' => 'in_progress',
                'priority' => 'medium',
                'progress_percentage' => 0,
            ]
        ];

        foreach ($goals as $goalData) {
            $goal = $user->goals()->create($goalData);

            // Create milestones based on the goal
            if ($goal->title === 'Learn Web Development') {
                $milestones = [
                    [
                        'title' => 'Frontend Development Basics',
                        'description' => 'Learn HTML, CSS, and JavaScript fundamentals',
                        'due_date' => Carbon::now()->addMonths(2),
                        'status' => 'in_progress',
                        'priority' => 'high',
                        'progress_percentage' => 0,
                    ],
                    [
                        'title' => 'Backend Development Fundamentals',
                        'description' => 'Master PHP and Laravel framework',
                        'due_date' => Carbon::now()->addMonths(4),
                        'status' => 'pending',
                        'priority' => 'high',
                        'progress_percentage' => 0,
                    ]
                ];
            } else {
                $milestones = [
                    [
                        'title' => 'Establish Exercise Routine',
                        'description' => 'Create and maintain a regular workout schedule',
                        'due_date' => Carbon::now()->addMonth(),
                        'status' => 'in_progress',
                        'priority' => 'high',
                        'progress_percentage' => 0,
                    ],
                    [
                        'title' => 'Nutrition Planning',
                        'description' => 'Develop and follow a balanced meal plan',
                        'due_date' => Carbon::now()->addMonths(2),
                        'status' => 'pending',
                        'priority' => 'medium',
                        'progress_percentage' => 0,
                    ]
                ];
            }

            foreach ($milestones as $milestoneData) {
                $milestone = $goal->milestones()->create($milestoneData);

                // Create tasks based on the milestone
                if ($milestone->title === 'Frontend Development Basics') {
                    $tasks = [
                        [
                            'title' => 'Complete HTML5 Course',
                            'description' => 'Learn semantic HTML and best practices',
                            'status' => 'in_progress',
                            'priority' => 'high',
                            'due_date' => Carbon::now()->addWeeks(2),
                        ],
                        [
                            'title' => 'Master CSS3 Fundamentals',
                            'description' => 'Learn layouts, flexbox, and grid systems',
                            'status' => 'pending',
                            'priority' => 'high',
                            'due_date' => Carbon::now()->addWeeks(4),
                        ],
                        [
                            'title' => 'JavaScript Basics',
                            'description' => 'Learn variables, functions, and DOM manipulation',
                            'status' => 'pending',
                            'priority' => 'medium',
                            'due_date' => Carbon::now()->addWeeks(6),
                        ],
                        [
                            'title' => 'Build Practice Projects',
                            'description' => 'Create small projects using HTML, CSS, and JS',
                            'status' => 'pending',
                            'priority' => 'medium',
                            'due_date' => Carbon::now()->addWeeks(8),
                        ],
                    ];
                } elseif ($milestone->title === 'Backend Development Fundamentals') {
                    $tasks = [
                        [
                            'title' => 'PHP Basics',
                            'description' => 'Learn PHP syntax and core concepts',
                            'status' => 'pending',
                            'priority' => 'high',
                            'due_date' => Carbon::now()->addWeeks(10),
                        ],
                        [
                            'title' => 'Laravel Installation & Setup',
                            'description' => 'Set up development environment for Laravel',
                            'status' => 'pending',
                            'priority' => 'high',
                            'due_date' => Carbon::now()->addWeeks(11),
                        ],
                        [
                            'title' => 'Laravel Routing & Controllers',
                            'description' => 'Master Laravel routing and controller concepts',
                            'status' => 'pending',
                            'priority' => 'medium',
                            'due_date' => Carbon::now()->addWeeks(13),
                        ],
                        [
                            'title' => 'Database & Eloquent ORM',
                            'description' => 'Learn database operations with Eloquent',
                            'status' => 'pending',
                            'priority' => 'medium',
                            'due_date' => Carbon::now()->addWeeks(15),
                        ],
                    ];
                } elseif ($milestone->title === 'Establish Exercise Routine') {
                    $tasks = [
                        [
                            'title' => 'Create Workout Schedule',
                            'description' => 'Plan weekly workout routine',
                            'status' => 'in_progress',
                            'priority' => 'high',
                            'due_date' => Carbon::now()->addDays(3),
                        ],
                        [
                            'title' => 'Join Gym',
                            'description' => 'Research and sign up for a suitable gym membership',
                            'status' => 'pending',
                            'priority' => 'high',
                            'due_date' => Carbon::now()->addWeek(),
                        ],
                        [
                            'title' => 'Complete First Week of Workouts',
                            'description' => 'Follow the planned routine for one week',
                            'status' => 'pending',
                            'priority' => 'medium',
                            'due_date' => Carbon::now()->addWeeks(2),
                        ],
                        [
                            'title' => 'Track Progress',
                            'description' => 'Record workouts and measurements',
                            'status' => 'pending',
                            'priority' => 'medium',
                            'due_date' => Carbon::now()->addWeeks(4),
                        ],
                    ];
                } else {
                    $tasks = [
                        [
                            'title' => 'Calculate Daily Calorie Needs',
                            'description' => 'Determine optimal calorie intake',
                            'status' => 'pending',
                            'priority' => 'high',
                            'due_date' => Carbon::now()->addDays(5),
                        ],
                        [
                            'title' => 'Create Meal Plan',
                            'description' => 'Design balanced weekly meal plan',
                            'status' => 'pending',
                            'priority' => 'high',
                            'due_date' => Carbon::now()->addWeeks(2),
                        ],
                        [
                            'title' => 'Grocery Shopping List',
                            'description' => 'Prepare list of healthy ingredients',
                            'status' => 'pending',
                            'priority' => 'medium',
                            'due_date' => Carbon::now()->addWeeks(2),
                        ],
                        [
                            'title' => 'Learn Meal Prep',
                            'description' => 'Research and practice meal preparation techniques',
                            'status' => 'pending',
                            'priority' => 'medium',
                            'due_date' => Carbon::now()->addWeeks(3),
                        ],
                    ];
                }

                foreach ($tasks as $taskData) {
                    $milestone->tasks()->create($taskData);
                }
            }
        }
    }
}
