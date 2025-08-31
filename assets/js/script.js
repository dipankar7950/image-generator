// Enhanced prompt suggestions
document.addEventListener('DOMContentLoaded', function() {
    const promptTextarea = document.getElementById('prompt');
    
    if (promptTextarea) {
        // Add prompt suggestions on focus
        promptTextarea.addEventListener('focus', function() {
            if (!this.getAttribute('data-suggestions-shown')) {
                this.placeholder = "Try: 'A futuristic cityscape at night with flying cars and neon lights'";
                this.setAttribute('data-suggestions-shown', 'true');
            }
        });
        
        // Add example prompts button
        const promptContainer = promptTextarea.parentNode;
        const examplesButton = document.createElement('button');
        examplesButton.type = 'button';
        examplesButton.className = 'btn btn-secondary';
        examplesButton.textContent = 'Show Examples';
        examplesButton.style.marginTop = '10px';
        
        examplesButton.addEventListener('click', function() {
            const examples = [
                "A mystical forest with glowing mushrooms and fairies",
                "A cyberpunk street market in Tokyo during rain",
                "A realistic portrait of a wise old wizard with a long beard",
                "A steampunk airship flying over Victorian London",
                "An astronaut exploring an alien planet with twin suns"
            ];
            
            const randomExample = examples[Math.floor(Math.random() * examples.length)];
            promptTextarea.value = randomExample;
        });
        
        promptContainer.appendChild(examplesButton);
    }
    
    // Image size ratio helper
    const sizeSelect = document.getElementById('size');
    if (sizeSelect) {
        sizeSelect.addEventListener('change', function() {
            const sizeValue = this.value;
            if (sizeValue) {
                const [width, height] = sizeValue.split('x');
                const ratio = (width / height).toFixed(2);
                
                // Create or update ratio indicator
                let ratioIndicator = document.getElementById('ratio-indicator');
                if (!ratioIndicator) {
                    ratioIndicator = document.createElement('div');
                    ratioIndicator.id = 'ratio-indicator';
                    ratioIndicator.style.marginTop = '10px';
                    ratioIndicator.style.fontSize = '0.9rem';
                    ratioIndicator.style.color = '#666';
                    this.parentNode.appendChild(ratioIndicator);
                }
                
                let aspect = '';
                if (ratio == 1) aspect = ' (Square)';
                else if (ratio > 1) aspect = ' (Landscape)';
                else aspect = ' (Portrait)';
                
                ratioIndicator.textContent = `Aspect ratio: ${width}:${height}${aspect}`;
            }
        });
    }
});
