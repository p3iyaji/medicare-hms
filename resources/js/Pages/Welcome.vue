<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white">
        <!-- Background Medical Image -->
        <div class="fixed inset-0 z-0 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-900/20 to-teal-900/10"></div>
            <img 
                src="images/medical-bg.jpg"
                alt="Medical equipment background"
                class="w-full h-full object-cover opacity-20"
            />
            <!-- Subtle grid pattern overlay -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zMCA2MGMxNi41NjkgMCAzMC0xMy40MzEgMzAtMzBTNDYuNTY5IDAgMzAgMCAwIDEzLjQzMSAwIDMwczEzLjQzMSAzMCAzMCAzem0wLTJjMTUuNDY0IDAgMjgtMTIuNTM2IDI4LTI4UzQ1LjQ2NCAyIDMwIDIgMiAxNC41MzYgMiAzMHMyLjUzNiAyOCAyOCAyOHoiIGZpbGw9IiMwMDczY2MiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvZz48L3N2Zz4=')]"></div>
        </div>

        <!-- Navigation -->
        <nav class="relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6 md:justify-start md:space-x-10">
                    <!-- Logo -->
                    <div class="flex justify-start lg:w-0 lg:flex-1">
                        <a href="/" class="flex items-center">
                            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            </div>
                            <span class="ml-3 text-2xl font-bold text-gray-900">MediCare Pro</span>
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center justify-end md:flex-1 lg:w-0">
                        <template v-if="$page.props.auth.user">
                            <a href="/dashboard" class="ml-8 whitespace-nowrap inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700">
                                Dashboard
                            </a>
                        </template>
                        <template v-else>
                            <a href="/login" class="whitespace-nowrap text-base font-medium text-gray-700 hover:text-blue-600">
                                Sign in
                            </a>
                            <a href="/register" class="ml-8 whitespace-nowrap inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700">
                                Get Started
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-16 text-center">
                <!-- Tagline -->
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-blue-100 text-blue-800 text-sm font-medium mb-8">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Trusted by over 500+ medical professionals
                </div>

                <!-- Main Heading -->
                <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                    <span class="block">Your Complete</span>
                    <span class="block text-blue-600">Medical Management</span>
                    <span class="block">Solution</span>
                </h1>

                <!-- Subheading -->
                <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                    Streamline patient care, manage appointments, track medical records, and enhance healthcare delivery with our comprehensive medical management platform.
                </p>

                <!-- CTA Buttons -->
                <div class="mt-10 max-w-md mx-auto sm:flex sm:justify-center md:mt-12">
                    <div class="rounded-md shadow">
                        <a href="/register" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10">
                            Start here!
                            <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                    <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
                        <a href="#features" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                            <svg class="mr-2 -ml-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Watch Demo
                        </a>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mt-16 grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">24/7</div>
                        <div class="mt-1 text-sm text-gray-500">Support Available</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">99.9%</div>
                        <div class="mt-1 text-sm text-gray-500">Uptime Guarantee</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">HIPAA</div>
                        <div class="mt-1 text-sm text-gray-500">Compliant</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">500+</div>
                        <div class="mt-1 text-sm text-gray-500">Happy Clients</div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Features Section -->
        <section id="features" class="relative z-10 py-16 bg-white/80 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                        Everything You Need for Modern Healthcare
                    </h2>
                    <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">
                        Comprehensive tools designed specifically for medical professionals and healthcare facilities.
                    </p>
                </div>

                <div class="mt-20">
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <!-- Feature 1 -->
                        <div class="relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-teal-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                            <div class="relative p-6 bg-white rounded-2xl">
                                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Patient Management</h3>
                                <p class="mt-2 text-gray-500">
                                    Comprehensive patient profiles, medical history tracking, and easy access to vital information.
                                </p>
                            </div>
                        </div>

                        <!-- Feature 2 -->
                        <div class="relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-teal-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                            <div class="relative p-6 bg-white rounded-2xl">
                                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Appointment Scheduling</h3>
                                <p class="mt-2 text-gray-500">
                                    Smart scheduling system with automated reminders and real-time availability.
                                </p>
                            </div>
                        </div>

                        <!-- Feature 3 -->
                        <div class="relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-teal-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                            <div class="relative p-6 bg-white rounded-2xl">
                                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Electronic Health Records</h3>
                                <p class="mt-2 text-gray-500">
                                    Secure digital records with easy access, sharing capabilities, and audit trails.
                                </p>
                            </div>
                        </div>

                        <!-- Feature 4 -->
                        <div class="relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-teal-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                            <div class="relative p-6 bg-white rounded-2xl">
                                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Billing & Insurance</h3>
                                <p class="mt-2 text-gray-500">
                                    Integrated billing system with insurance claim processing and payment tracking.
                                </p>
                            </div>
                        </div>

                        <!-- Feature 5 -->
                        <div class="relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-teal-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                            <div class="relative p-6 bg-white rounded-2xl">
                                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Lab Integration</h3>
                                <p class="mt-2 text-gray-500">
                                    Connect with laboratory systems for seamless test ordering and result management.
                                </p>
                            </div>
                        </div>

                        <!-- Feature 6 -->
                        <div class="relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-teal-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                            <div class="relative p-6 bg-white rounded-2xl">
                                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Analytics & Reports</h3>
                                <p class="mt-2 text-gray-500">
                                    Advanced analytics dashboard for insights into practice performance and patient trends.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="relative z-10 py-16 bg-gradient-to-br from-blue-50 to-teal-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                        Trusted by Healthcare Professionals
                    </h2>
                    <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">
                        See what medical professionals are saying about MediCare Pro.
                    </p>
                </div>

                <div class="mt-20 grid gap-8 lg:grid-cols-3">
                    <div class="bg-white rounded-2xl p-8 shadow-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-bold">DR</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Dr. Sarah Wilson</h4>
                                <p class="text-gray-500">Cardiologist, St. Mary's Hospital</p>
                            </div>
                        </div>
                        <p class="mt-4 text-gray-600 italic">
                            "MediCare Pro has transformed how we manage patient care. The intuitive interface and comprehensive features have improved our efficiency by 40%."
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-8 shadow-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-bold">RN</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Jennifer Miller</h4>
                                <p class="text-gray-500">Head Nurse, City General</p>
                            </div>
                        </div>
                        <p class="mt-4 text-gray-600 italic">
                            "The appointment scheduling system alone has saved us hours each week. Our patients love the automated reminders and easy rescheduling."
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-8 shadow-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-bold">AD</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Michael Chen</h4>
                                <p class="text-gray-500">Clinic Administrator</p>
                            </div>
                        </div>
                        <p class="mt-4 text-gray-600 italic">
                            "From billing to patient records, everything is seamlessly integrated. The analytics dashboard gives us invaluable insights into our practice."
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA Section -->
        <section class="relative z-10 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-blue-600 to-teal-500 rounded-3xl shadow-xl overflow-hidden">
                    <div class="px-6 py-12 sm:px-12 sm:py-16 lg:py-20 lg:px-16">
                        <div class="lg:flex lg:items-center lg:justify-between">
                            <div class="lg:w-0 lg:flex-1">
                                <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                                    Ready to transform your healthcare practice?
                                </h2>
                                <p class="mt-4 max-w-3xl text-lg text-blue-100">
                                    Join thousands of medical professionals who trust MediCare Pro for their practice management needs.
                                </p>
                            </div>
                            <div class="mt-8 lg:mt-0 lg:ml-8">
                                <div class="inline-flex rounded-md shadow">
                                    <a href="/register" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50">
                                        Get Started Free
                                        <svg class="ml-3 -mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </a>
                                </div>
                                <div class="mt-3 inline-flex rounded-md shadow sm:mt-0 sm:ml-3">
                                    <a href="/login" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 bg-opacity-25 hover:bg-opacity-30">
                                        Schedule a Demo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 bg-gray-900">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="col-span-2">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            </div>
                            <span class="ml-3 text-xl font-bold text-white">MediCare Pro</span>
                        </div>
                        <p class="mt-4 text-gray-400 max-w-md">
                            Comprehensive medical management solution designed to streamline healthcare delivery and improve patient outcomes.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold text-gray-200 tracking-wider uppercase">Product</h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="#" class="text-base text-gray-400 hover:text-white">Features</a></li>
                            <li><a href="#" class="text-base text-gray-400 hover:text-white">Pricing</a></li>
                            <li><a href="#" class="text-base text-gray-400 hover:text-white">API</a></li>
                            <li><a href="#" class="text-base text-gray-400 hover:text-white">Documentation</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold text-gray-200 tracking-wider uppercase">Company</h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="#" class="text-base text-gray-400 hover:text-white">About</a></li>
                            <li><a href="#" class="text-base text-gray-400 hover:text-white">Blog</a></li>
                            <li><a href="#" class="text-base text-gray-400 hover:text-white">Careers</a></li>
                            <li><a href="#" class="text-base text-gray-400 hover:text-white">Contact</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-12 border-t border-gray-700 pt-8">
                    <p class="text-base text-gray-400 text-center">
                        &copy; 2026 MediCare Pro. All rights reserved. HIPAA compliant. Patient data secured.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
})
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #3b82f6;
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: #2563eb;
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Gradient text animation */
@keyframes gradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

.animate-gradient {
    background-size: 200% 200%;
    animation: gradient 3s ease infinite;
}
</style>