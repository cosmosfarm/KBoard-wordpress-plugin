(function(){
	function initialize(root){
		var images = [];
		if(root.matches && root.matches('img[src$="#kboard-altcha"]')) images.push(root);
		if(root.querySelectorAll) images = images.concat(Array.prototype.slice.call(root.querySelectorAll('img[src$="#kboard-altcha"]')));
		images.forEach(function(image){
			var form = image.closest('form');
			var input = form && form.querySelector('input[name="captcha"]');
			if(!input) return;
			var widget = document.createElement('altcha-widget');
			widget.setAttribute('challenge', kboard_altcha_settings.challenge);
			widget.setAttribute('language', kboard_altcha_settings.language);
			widget.setAttribute('name', 'altcha');
			input.value = 'ALTCHA';
			input.disabled = true;
			input.hidden = true;
			input.style.display = 'none';
			if(image.parentNode.tagName === 'LABEL'){
				var label = image.parentNode;
				label.parentNode.insertBefore(widget, label);
				label.hidden = true;
				label.style.display = 'none';
			}
			else image.parentNode.replaceChild(widget, image);
		});
	}
	function ready(){
		initialize(document);
		if(window.MutationObserver){
			new MutationObserver(function(changes){
				changes.forEach(function(change){
					Array.prototype.forEach.call(change.addedNodes, function(node){
						if(node.nodeType === 1) initialize(node);
					});
				});
			}).observe(document.body, {childList:true, subtree:true});
		}
	}
	if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
	else ready();
})();
