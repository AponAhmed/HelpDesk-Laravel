import { Dombuilder as el } from "@aponahmed/dombuilder";
import { marked } from 'marked'; // Import the marked library for Markdown conversion


export default class AiWindow {
    constructor({ ...obj }) {
        this.onResponse = obj.onResponse || null;
        this.onResponseUse = obj.onResponseUse || null;
        this.dom = null;
        this.init();
    }

    init() {
        console.log('Creating AI Window');

        // Create the modal structure using el
        this.dom = new el('div')
            .classes(['fixed', 'top-0', 'right-0', 'h-full', 'z-50', 'transform', 'translate-x-full', 'transition-transform', 'duration-300', 'ease-out']) // Tailwind for overlay and slide-in effect
            .append(
                new el('div')
                    .classes(['bg-[#1a1f30]', 'px-6', 'h-full', 'w-[450px]', 'shadow-lg', 'w-full', 'max-w-sm', 'md:max-w-full']) // Popup container, max width set to 350px (max-w-sm)
                    .append(
                        new el('div').classes(['w-full', 'flex', 'justify-between', 'py-2'])
                            .append(
                                new el('h3')
                                    .classes(['text-xl', 'font-semibold', 'text-gray-300'])
                                    .html('Compose by AI')
                                    .element
                            ).append(new el('span')
                                .classes(['text-red-400', 'text-xl', 'cursor-pointer'])
                                .html('&times;')
                                .event('click', () => {
                                    this.dom.element.remove();
                                })
                                .element)
                            .element
                    )
                    .append(new el('div').classes(['flex', 'flex-col', 'w-full', 'h-full'])
                        .append(new el('div').classes(['flex', 'flex-col', 'w-full', 'h-2/6'])//Generate Area
                            .append(
                                new el('textarea')
                                    .attr('placeholder', 'Enter your prompt...')
                                    .classes(['w-full', 'h-full', 'bg-gray-700', 'text-white', 'p-2', 'mb-4', 'border', 'rounded', 'focus:outline-none'])
                                    .attr('id', 'ai-prompt')
                                    .element
                            )
                            .append(
                                new el('div').classes(['flex', 'flex-row', 'justify-between']).append(
                                    new el('button')
                                        .classes(['bg-gray-900', 'mb-2', 'border', 'border-gray-950', 'text-gray-300', 'px-4', 'py-2', 'rounded', 'hover:bg-gray-800', 'mr-2'])
                                        .html('Generate')
                                        .event('click', (e) => this.generateResponse(e.target)) // Generate button action
                                        .element
                                ).element
                            ).element
                        )
                        .append(new el('hr').classes(['border-gray-900']).element)
                        .append(new el('div').classes(['flex', 'flex-col', 'w-full', 'h-4/6'])//response area
                            .append(
                                new el('textarea')
                                    .attr('placeholder', 'AI Response...')
                                    .classes(['w-full', 'h-[calc(100%-110px)]', 'bg-gray-700', 'text-white', 'p-2', 'my-4', 'border', 'rounded', 'bg-gray-100'])
                                    .attr('id', 'ai-response')
                                    .attr('readonly', true) // Set as readonly
                                    .element
                            ).append(
                                new el('div').classes(['flex', 'flex-row', 'justify-between']).append(
                                    new el('button')
                                        .classes(['bg-green-500', 'text-white', 'px-4', 'py-2', 'rounded', 'hover:bg-green-600'])
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
