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
                            <select id="aiProvider" name="optionGlobal[ai_provider]" id="select"
                                class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950">
                                <option class="dark:text-gray-300" value="freebox"
                                    @if ($Settings::get('ai_provider', 'gemini', true) === 'freebox') selected @endif>Open AI
                                    (Freebox)
                                </option>
                                <option class="dark:text-gray-300" value="gemini"
                                    @if ($Settings::get('ai_provider', 'gemini', true) === 'gemini') selected @endif>Gemini
                                </option>
                                <option class="dark:text-gray-300" value="openai"
                                    @if ($Settings::get('ai_provider', 'gemini', true) === 'openai') selected @endif>OpenAi
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
                                            type="text" name="optionGlobal[ai_api_key]" value="<?php echo $Settings::get('ai_api_key', '', true); ?>">
                                        <span class="text-gray-500 text-sm">Api Key for Google - Gemini </span>
                                    </div>
                                </div>
                                <div
                                    class="openai-settings optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">API KEY</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <input
                                            class="w-full border rounded px-2 py-1  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                            type="text" name="optionGlobal[ai_api_key_openai]"
                                            value="<?php echo $Settings::get('ai_api_key_openai', '', true); ?>">
                                        <span class="text-gray-500 text-sm">Api Key for OpenAI</span>
                                    </div>
                                </div>
                                <div
                                    class="openai-settings optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">Model</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <input
                                            class="w-full border rounded px-2 py-1  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                            type="text" name="optionGlobal[ai_model_openai]"
                                            value="<?php echo $Settings::get('ai_model_openai', 'gpt-3.5-turbo', true); ?>">
                                        <span class="text-gray-500 text-sm">AI model</span>
                                    </div>
                                </div>
                                <div
                                    class="gemini-settings optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">Data Model</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <input
                                            class="w-full border rounded px-2 py-1  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                            type="text" name="optionGlobal[ai_data_model]"
                                            value="<?php echo $Settings::get('ai_data_model', 'gemini-pro', true); ?>">
                                        <span class="text-gray-500 text-sm">Data model of service provider</span>
                                    </div>
                                </div>
                                <div
                                    class="freebox-settings optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">FreeBox Model</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        @php
                                            $selectedModel = $Settings::get(
                                                'ai_freebox_model',
                                                'ai-content-generator',
                                                true,
                                            ); // Assuming $Settings::get() retrieves the selected language

                                            $models = [
                                                'ai-content-generator' => 'Content Generator',
                                                'ai-email-generator' => 'Email Generator',
                                            ];
                                        @endphp
                                        <select name="optionGlobal[ai_freebox_model]"
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
                                            $selectedLanguage = $Settings::get('ai_lang', '', true); // Assuming $Settings::get() retrieves the selected language
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

                                        <select name="optionGlobal[ai_lang]"
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
                                        <select name="optionGlobal[ai_tone]"
                                            class="block w-full p-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950">
                                            <option class="dark:text-gray-300" value="" disabled>Select an option
                                            </option>
                                            @php
                                                $selectedOption = $Settings::get('ai_tone', 'Formal', true); // Assume $selectedOption contains the value of the selected option
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
                                    class="gemini-settings openai-settings optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">Creativity</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <div class="flex">
                                            @php
                                                $creativity = $Settings::get('ai_temperature', '0.7', true);
                                            @endphp
                                            <input title="Temperature" name="optionGlobal[ai_temperature]"
                                                id="temparature" type="range" min="0" max="1"
                                                value=" {{ $creativity }}" step="0.1"
                                                class="mt-2 range-input appearance-none w-10/12 bg-gray-400 rounded h-1 transition-all ease-in-out duration-300  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                                oninput="document.getElementById('temparatureVal').textContent = this.value">

                                            <span id="temparatureVal" class="text-sm ml-2">
                                                {{ $creativity }}
                                            </span>
                                        </div>

                                        {{-- @dd($Settings::get('ai_temperature', '0.7')); --}}
                                        <span class="text-gray-500 text-sm">Controls the randomness of the output. Must
                                            be positive. Typical values are in the range: [0.0,1.0]</span>
                                    </div>
                                </div>
                                <div class="optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">Prompt Prefix</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <textarea rows="2" name="optionGlobal[ai_prompt_prefix]"
                                            class="p-2 rounded border border-gray-300 bg-transparent w-full h-full  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                            placeholder="Write a reply in short-sentence to this email using the hints below:"><?php echo $Settings::get('ai_prompt_prefix', '', true); ?></textarea>

                                        <span class="text-gray-500 text-sm">Prefix text of prompt</span>
                                    </div>
                                </div>
                                <div class="optionField flex flex-col md:flex-row md:items-center justify-start mb-4">
                                    <label class="w-32  text-gray-700 dark:text-gray-300">Signature Filter</label>
                                    <!-- Adjust the width as needed -->
                                    <div class="flex-1 md:ml-4 ml-0">
                                        <textarea placeholder="Best regards," rows="2" name="optionGlobal[ai_signeture_prefix]"
                                            class="p-2 scrollbar-thin rounded border border-gray-300 bg-transparent w-full h-full  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                            placeholder="Write a reply in short-sentence to this email using the hints below:"><?php echo $Settings::get('ai_signeture_prefix', '', true); ?></textarea>

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
                                            $aboutField = $Settings::get('ai_about', 'ai_about_company', true);
                                        @endphp
                                    </div>
                                    <textarea id="aboutData" rows="15" name="optionGlobal[{{ $aboutField }}]"
                                        class="scrollbar-thin p-2 rounded border border-gray-300 bg-transparent w-full h-full  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-950"
                                        placeholder="About Your Company"><?php echo $Settings::get($aboutField, '', true); ?></textarea>
                                </div>
                            </div>

                        </div>
                        <div class="data-table-footer settings">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="script">
        <script type="module">
            document.addEventListener("DOMContentLoaded", function() {
                $("#settingsForm").on('submit', function(e) {
                    e.preventDefault();
                    let route = $(this).attr('action');
                    let settings = $(this).serialize();
                    let target = $(e.target);
                    target.find("button[type='submit']").html('<div class="load load03"><div></div></div>');
                    axios.post(route, {
                        settings
                    }).then((response) => {
                        target.find("button[type='submit']").html("Update");
                        //console.log(response);
                        if (response.data == 1) {
                            ntf('Settings Updated', 'success');
                        }
                    }).catch((error) => {
                        ntf(error, 'error');
                    });
                });
            });
        </script>
    </x-slot>


</x-app-layout>
