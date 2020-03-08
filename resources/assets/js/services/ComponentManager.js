
import draggable from 'vuedraggable';
import vSelect from "vue-select";
import Editor from '@tinymce/tinymce-vue';

class ComponentManager
{
	constructor()
	{
		this.components = {};
	}

	registerComponents()
	{
		this.registerInternalComponents()
		this.registerExternalPackageComponents();

		return this.components;
	}

	registerInternalComponents()
	{
		const files = require.context('../vue/', true, /\.vue$/i);

		files.keys().map((key) => {
			let componentName = key.split('/').pop().split('.')[0];
			this.components[componentName] = Vue.component(componentName, files(key).default);
		});
	}

	registerExternalPackageComponents()
{
	vSelect.props.components.default = () => ({
		OpenIndicator: {
			render: (createElement) => {
				return createElement('span', {}, [ createElement('b') ]);
			}
		}
	});

	this.components['v-select'] = Vue.component('v-select', vSelect);
	this.components['draggable'] = Vue.component('draggable', draggable);
	this.components['tinymce-editor'] = Vue.component('tinymce-editor', Editor);
}
}

export default ComponentManager;