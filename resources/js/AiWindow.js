import { Dombuilder as el } from "@aponahmed/dombuilder";
import { marked } from 'marked'; // Import the marked library for Markdown conversion


export default class AiWindow {
    constructor({ ...obj }) {
        this.onResponse = obj.onResponse || null;
        this.onResponseUse = obj.onResponseUse || null;
        this.dom = null;
        this.aiProviders = { 'gemini': 'Gemini', 'openai': 'OpenAI', 'freebox': 'FreeBox' };
        this.init();
    }


    init() {
        // Create the modal structure using el
        this.dom = new el('div')
            .classes(['fixed', 'top-2', 'right-2', 'h-[calc(100%-16px)]', 'bg-[#1a1f30]', 'rounded-md', 'z-50', 'transform', 'translate-x-full', 'transition-transform', 'duration-300', 'ease-out']) // Tailwind for overlay and slide-in effect
            .append(
                new el('div')
                    .classes(['px-6', 'pt-3', 'h-full', 'w-[450px]', 'shadow-lg', 'max-w-sm', 'md:max-w-full']) // Popup container, max width set to 350px (max-w-sm)
                    .append(
                        new el('div').classes(['w-full', 'flex', 'justify-between', 'py-2'])
                            .append(
                                new el('h3')
                                    .classes(['text-xl', 'font-semibold', 'text-gray-300', 'mb-2'])
                                    .html('Compose by AI')
                                    .element
                            ).append(new el('span')
                                .classes(['text-red-400', 'text-2xl', 'cursor-pointer', 'absolute', 'right-4', 'top-2'])
                                .html('&times;')
                                .event('click', () => {
                                    this.dom.element.remove();
                                })
                                .element)
                            .element
                    )
                    .append(new el('div').classes(['flex', 'flex-col', 'w-full', 'h-full'])
                        .append(new el('div').classes(['flex', 'flex-col', 'w-full', 'h-3/6'])//Generate Area
                            .append(
                                new el('textarea')
                                    .attr('placeholder', 'Enter your prompt...')
                                    .classes(['w-full', 'h-full', 'bg-gray-700', 'text-white', 'p-2', 'mb-4', 'border', 'rounded', 'focus:outline-none'])
                                    .attr('id', 'ai-prompt')
                                    .element
                            )

                            .append(
                                new el('div').classes(['flex', 'flex-row', 'mb-2', 'justify-between']).append(
                                    new el('button')
                                        .classes(['bg-gray-900', 'border', 'border-gray-950', 'text-gray-300', 'px-4', 'py-2', 'rounded', 'hover:bg-gray-800', 'mr-2'])
                                        .html('Generate')
                                        .event('click', (e) => this.generateResponse(e.target)) // Generate button action
                                        .element
                                ).append(this.aiSettings()).element
                            ).element
                        )
                        .append(new el('hr').classes(['border-gray-900']).element)
                        .append(new el('div').classes(['flex', 'flex-col', 'w-full', 'h-4/6'])//response area
                            .append(
                                new el('textarea')
                                    .attr('placeholder', 'AI Response...')
                                    .classes(['w-full', 'h-[calc(100%-125px)]', 'bg-gray-700', 'text-white', 'p-2', 'my-4', 'border', 'rounded', 'bg-gray-100'])
                                    .attr('id', 'ai-response')
                                    .attr('readonly', true) // Set as readonly
                                    .element
                            ).append(
                                new el('div').classes(['flex', 'flex-row', 'justify-between']).append(
                                    new el('button')
                                        .classes(['bg-gray-900', 'mb-2', 'border', 'border-gray-950', 'text-gray-300', 'px-4', 'py-2', 'rounded', 'hover:bg-gray-800', 'mr-2'])
                                        .html('Use')
                                        .event('click', () => this.useResponse()) // Use button action
                                        .element
                                ).element
                            ).element
                        ).element
                    ).element
            )
            .renderTo(document.body); // Append popup to body

        // Trigger the slide-in effect after appending
        setTimeout(() => {
            this.dom.element.classList.remove('translate-x-full'); // Slide in from right
        }, 10); // Small delay for the transition to trigger
    }

    aiSettings() {
        // AI Providers Dropdown
        const aiProviderSelect = new el('select')
            .classes(['bg-gray-800', 'w-24', 'text-white', 'text-sm', 'px-2', 'mx-2', 'my-1', 'py-0', 'rounded-lg', 'border-gray-900'])
            .attr('id', 'ai-provider-select')
            .event('change', (e) => this.handleSettingChange('ai_provider', e.target.value));

        Object.entries(this.aiProviders).forEach(([value, label]) => {
            let optionElement = new el('option')
                .attr('value', value)
                .html(label);

            // Check if the value matches the current AI provider and set 'selected' attribute
            if (value === AI_OPTIONS.ai_provider) {
                optionElement.attr('selected', 'selected');
            }

            aiProviderSelect.append(optionElement.element);
        });

        // Tone Dropdown (only shown for FreeBox)
        const toneSelect = new el('select')
            .classes(['bg-gray-800', 'w-40', 'text-white', 'text-sm', 'px-2', 'my-1', 'py-0', 'rounded-lg', 'border-gray-900'])
            .attr('id', 'tone-select')
            .styles({ display: 'none' }) // Hidden initially
            .event('change', (e) => this.handleSettingChange('ai_tone', e.target.value));

        // Populate Tone options
        Object.entries({
            'Formal': 'Formal',
            'Professional': 'Professional',
            'Friendly': 'Friendly',
            'Concise': 'Concise',
            'Detailed': 'Detailed',
            'Informal': 'Informal',
            'Inspirational': 'Inspirational',
            'Requestive': 'Requestive',
            'Consultative': 'Consultative',
            'Appreciative': 'Appreciative',
            'Declination': 'Declination'
        }).forEach(([value, label]) => {
            let optionElement = new el('option')
                .attr('value', value)
                .html(label);

            // Check if the value matches the current AI tone and set 'selected' attribute
            if (value === AI_OPTIONS.ai_tone) {
                optionElement.attr('selected', 'selected');
            }

            toneSelect.append(optionElement.element);
        });


        // Temperature Slider (for Gemini and OpenAI)
        // Temperature Slider (for Gemini and OpenAI)
        const tempSlider = new el('input')
            .attr('type', 'range')
            .attr('min', '0')
            .attr('max', '1')
            .attr('step', '0.1')
            .attr('value', AI_OPTIONS.ai_temperature) // Set the initial value based on AI_OPTIONS.creativity
            .classes(['bg-gray-800', 'text-white', 'w-40'])
            .event('input', (e) => this.handleSettingChange('ai_temperature', e.target.value));

        // Container for all settings
        this.aisettingsDom = new el('div').classes(['ai-settings', 'flex']);

        // Add elements to the container
        this.aisettingsDom
            .append(aiProviderSelect.element)
            .append(toneSelect.element)
            .append(tempSlider.element);

        this.optionSelect(AI_OPTIONS.ai_provider, toneSelect, tempSlider);
        // Event to show tone options only when FreeBox is selected
        aiProviderSelect.event('change', (e) => {
            this.optionSelect(e.target.value, toneSelect, tempSlider);
        });

        // Initial axios request to store changes
        this.handleSettingChange = (SettingsName, value) => {
            AI_OPTIONS[SettingsName] = value;
            axios.post('/settings/general', {
                settings: {
                    'optionGlobal': {
                        [SettingsName]: value
                    }
                }
            }
            ).then(response => {
                console.log('Settings saved:', response.data);
            }).catch(error => {
                console.error('Error saving settings:', error);
            });
        };

        return this.aisettingsDom.element;
    }

    optionSelect(value, toneSelect, tempSlider) {
        if (value === 'freebox') {
            toneSelect.styles({ display: 'block' });
            tempSlider.styles({ display: 'none' });
        } else {
            toneSelect.styles({ display: 'none' });
            tempSlider.styles({ display: 'block' });
        }
    }

    generateResponse(target) {
        const prompt = document.getElementById('ai-prompt').value;
        target.innerHTML = "Please Wait...";
        if (prompt.trim()) {
            // Prepare the request payload
            const data = { prompt: prompt };

            // Send the prompt to the Laravel route using Axios
            axios.post('/ai', data)
                .then(response => {
                    // Assuming the AI response comes back in the 'data' field
                    document.getElementById('ai-response').value = response.data.response;
                    target.innerHTML = "re-Generate";
                    if (this.onResponse) {
                        this.onResponse(response.data.response); // Trigger the callback if set
                    }
                })
                .catch(error => {
                    console.error('Error generating AI response:', error);
                    alert('There was an issue generating the response. Please try again.');
                });
        } else {
            alert('Please enter a prompt.');
        }
    }

    useResponse() {
        const response = document.getElementById('ai-response').value;

        if (response) {
            // Convert the Markdown response to HTML using marked
            const htmlResponse = marked(response);

            if (this.onResponseUse) {
                this.onResponseUse(htmlResponse); // Send the converted HTML response
            }

            this.dom.element.remove(); // Remove the popup
            // Add any additional logic here
        }
    }

}
