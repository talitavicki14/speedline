window.showImageFull = function(src) {
    document.getElementById('fullImageSrc').src = src;
    if (typeof openModal === 'function') {
        openModal('fullImageModal');
    }
}

window.updateCharCount = function(textarea, counterId) {
    const counter = document.getElementById(counterId);
    const remaining = 200 - textarea.value.length;
    counter.textContent = remaining;
    
    if (remaining < 20) {
        counter.classList.add('text-red-500');
        counter.classList.remove('text-slate-300');
    } else {
        counter.classList.remove('text-red-500');
        counter.classList.add('text-slate-300');
    }
}

window.openEditHero = function(hero) {
    const form = document.getElementById('editHeroForm');
    form.action = `/admin/heroes/${hero.id}`;
    
    document.getElementById('edit_hero_title').value = hero.title;
    const subtitleField = document.getElementById('edit_hero_subtitle');
    subtitleField.value = hero.subtitle || '';
    updateCharCount(subtitleField, 'edit_char_count');

    document.getElementById('edit_hero_order').value = hero.order;
    document.getElementById('edit_hero_active').checked = !!hero.is_active;

    document.getElementById('edit_preview').src = `/storage/${hero.image_url}`;
    
    if (typeof openModal === 'function') {
        openModal('editModal');
    }
}
