<x-app-layout title="Settings : AI Settings">

    <div>
        <div class="user-list data-list">
            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-title">
                        <h2>AI Settings</h2>
                    </div>
                    <div class="data-controller">
                        <!--<a class="popup btn-new" data-w='500'  href="">New</a><br>-->
                    </div>
                </div>
                <div class="optionGeneralArea">
                    <form method="post" id="settingsForm" action="{{ route('generalSettingsStore') }}">

                        <div class="flex items-center">
                            <label for="select" class="mr-2  text-gray-700 dark:text-gray-300">Select an
                                option:</label>
                            <select id="aiProvider" name="settings[ai_provider]" id="select"
                                class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950">
                                <option class="dark:text-gray-300" value="freebox"
                                    @if ($Settings::get('ai_provider', 'gemini') === 'freebox') selected @endif>Open AI
                                    (Freebox)
                                </option>
                                <option class="dark:text-gray-300" value="gemini"
                                    @if ($Settings::get('ai_provider', 'gemini') === 'gemini') selected @endif>Gemini
                                </option>
                            </select>
                        </div>
                        <hr class="my-4 border-gray-300 dark:border-gray-700">

                        <div class="flex flex-col md:flex-row gap-7">
                            <div class="w-full md:w-5/12 ">
                                <div
                                    class="gemini-settings optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">API KEY</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <input
                                            class="w-full border rounded px-2 py-1  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                            type="text" name="settings[ai_api_key]" value="<?php echo $Settings::get('ai_api_key', ''); ?>">
                                        <span class="text-gray-500 text-sm">Key for api service provider</span>
                                    </div>
                                </div>

                                <div
                                    class="gemini-settings optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">Data Model</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <input
                                            class="w-full border rounded px-2 py-1  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                            type="text" name="settings[ai_data_model]" value="<?php echo $Settings::get('ai_data_model', 'gemini-pro'); ?>">
                                        <span class="text-gray-500 text-sm">Data model of service provider</span>
                                    </div>
                                </div>
                                <div
                                    class="freebox-settings optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">FreeBox Model</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        @php
                                            $selectedModel = $Settings::get('ai_freebox_model', 'ai-content-generator'); // Assuming $Settings::get() retrieves the selected language

                                            $models = [
                                                'ai-content-generator' => 'Content Generator',
                                                'ai-email-generator' => 'Email Generator',
                                            ];
                                        @endphp
                                        <select name="settings[ai_freebox_model]"
                                            class="block w-full p-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950">
                                            @foreach ($models as $k => $name)
                                                <option class="dark:text-gray-300" value="{{ $k }}"
                                                    {{ $k == $selectedModel ? 'selected' : '' }}>
                                                    {{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div
                                    class="freebox-settings optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">Language</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        @php
                                            $selectedLanguage = $Settings::get('ai_lang', ''); // Assuming $Settings::get() retrieves the selected language
                                            $languages = [
                                                'English',
                                                'Bulgarian',
                                                'Czech',
                                                'Chinese (Simplified)',
                                                'Chinese (Traditional)',
                                                'Dutch',
                                                'Danish',
                                                'Estonian',
                                                'French',
                                                'Finnish',
                                                'German',
                                                'Greek',
                                                'Hungarian',
                                                'Italian',
                                                'Japanese',
                                                'Korean',
                                                'Lithuanian',
                                                'Latvian',
                                                'Norwegian',
                                                'Polish',
                                                'Portuguese (Portugal)',
                                                'Portuguese (Brazil)',
                                                'Romanian',
                                                'Spanish',
                                                'Slovak',
                                                'Slovenian',
                                                'Swedish',
                                            ];
                                        @endphp

                                        <select name="settings[ai_lang]"
                                            class="block w-full p-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950">
                                            @foreach ($languages as $language)
                                                <option class="dark:text-gray-300" value="{{ $language }}"
                                                    {{ $language == $selectedLanguage ? 'selected' : '' }}>
                                                    {{ $language }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div
                                    class="freebox-settings optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">Tone</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <select name="settings[ai_tone]"
                                            class="block w-full p-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950">
                                            <option class="dark:text-gray-300" value="" disabled>Select an option
                                            </option>
                                            @php
                                                $selectedOption = $Settings::get('ai_tone', 'Formal'); // Assume $selectedOption contains the value of the selected option
                                                $options = [
                                                    'Formal' => 'Formal',
                                                    'Professional' => 'Professional',
                                                    'Friendly' => 'Friendly',
                                                    'Concise' => 'Concise',
                                                    'Detailed' => 'Detailed',
                                                    'Informal' => 'Informal',
                                                    'Inspirational' => 'Inspirational',
                                                    'Requestive' => 'Requestive',
                                                    'Consultative' => 'Consultative',
                                                    'Appreciative' => 'Appreciative',
                                                    'Declination' => 'Declination',
                                                ];
                                            @endphp
                                            @foreach ($options as $value => $label)
                                                <option class="dark:text-gray-300" value="{{ $value }}"
                                                    {{ $selectedOption === $value ? 'selected' : '' }}>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div
                                    class="gemini-settings optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">Creativity</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <div class="flex">
                                            <input title="Temperature" name="settings[ai_temperature]" id="temparature"
                                                type="range" min="0" max="1"
                                                value="{{ $Settings::get('ai_temperature', '0.7') }}" step="0.1"
                                                class="mt-2 range-input appearance-none w-10/12 bg-gray-400 rounded h-1 transition-all ease-in-out duration-300  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                                oninput="document.getElementById('temparatureVal').textContent = this.value">

                                            <span id="temparatureVal" class="text-sm ml-2">
                                                {{ $Settings::get('ai_temperature', '0.7') }}
                                            </span>
                                        </div>

                                        {{-- <input class="w-full border rounded px-2 py-1" type="text"
                                        name="settings[ai_temperature]" value="<?php echo $Settings::get('ai_temperature', '0.7'); ?>">
                                     --}}
                                        <span class="text-gray-500 text-sm">Controls the randomness of the output. Must
                                            be positive. Typical values are in the range: [0.0,1.0]</span>
                                    </div>
                                </div>
                                <div class="optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">Prompt Prefix</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <textarea rows="2" name="settings[ai_prompt_prefix]"
                                            class="p-2 rounded border border-gray-300 bg-transparent w-full h-full  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                            placeholder="Write a reply in short-sentence to this email using the hints below:"><?php echo $Settings::get('ai_prompt_prefix', ''); ?></textarea>

                                        <span class="text-gray-500 text-sm">Prefix text of prompt</span>
                                    </div>
                                </div>
                                <div class="optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">Signature Filter</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <textarea placeholder="Best regards," rows="2" name="settings[ai_signeture_prefix]"
                                            class="p-2 scrollbar-thin rounded border border-gray-300 bg-transparent w-full h-full  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                            placeholder="Write a reply in short-sentence to this email using the hints below:"><?php echo $Settings::get('ai_signeture_prefix', ''); ?></textarea>

                                        <span class="text-gray-500 text-sm">(each should new Line) Prefix text of
                                            signature and remove rest..</span>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full md:w-7/12 mt-8 md:mt-0">
                                <div class="flex-column">
                                    <div class="flex mb-1">
                                        <label
                                            class=" block  text-gray-700 font-weight-bold dark:text-gray-300">Information
                                            About your Company </label>
                                        @php
                                            $aboutField = $Settings::get('ai_about', 'ai_about_company');
                                        @endphp
                                        <select id="aboutSwitch" name="settings[ai_about]"
                                            class="ml-4 px-1 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950">
                                            <option {{ $aboutField == 'ai_about_company' ? 'selected' : '' }}
                                                class="dark:text-gray-300" value="ai_about_company">Default
                                            </option>
                                            <option {{ $aboutField == 'ai_about_company_faq' ? 'selected' : '' }}
                                                class="dark:text-gray-300" value="ai_about_company_faq">Faq
                                            </option>
                                            <option {{ $aboutField == 'ai_about_company_new' ? 'selected' : '' }}
                                                class="dark:text-gray-300" value="ai_about_company_new">New Info
                                            </option>
                                        </select>
                                        <label id="waitMsg" class="hidden ml-2 text-red-400">Please Wait...</label>
                                    </div>
                                    <textarea id="aboutData" rows="12" name="settings[{{ $aboutField }}]"
                                        class="scrollbar-thin p-2 rounded border border-gray-300 bg-transparent w-full h-full  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                        placeholder="About Your Company"><?php echo $Settings::get($aboutField, ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="script">
        <script type="module"></script>
    </x-slot>


</x-app-layout>
