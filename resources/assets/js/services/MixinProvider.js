
import FormErrorKeysMixin from "../mixins/FormErrorKeysMixin";
import FormFieldMixin from "../mixins/FormFieldMixin";
import LabelAndInputWidthMixin from "../mixins/LabelAndInputWidthMixin";
import LocalizedFieldPropertyMixin from "../mixins/LocalizedFieldPropertyMixin";
import OverlayMixin from "../mixins/OverlayMixin";
import QuickSearchMixin from "../mixins/QuickSearchMixin";
import RefreshEntityObjectDataMixin from "../mixins/RefreshEntityObjectDataMixin";
import SortableMixin from "../mixins/SortableMixin";

class MixinProvider
{
	constructor()
	{
		this.mixins = {};
	}

	registerMixins()
	{
		this.mixins['FormErrorKeysMixin'] = FormErrorKeysMixin;
		this.mixins['FormFieldMixin'] = FormFieldMixin;
		this.mixins['LabelAndInputWidthMixin'] = LabelAndInputWidthMixin;
		this.mixins['LocalizedFieldPropertyMixin'] = LocalizedFieldPropertyMixin;
		this.mixins['OverlayMixin'] = OverlayMixin;
		this.mixins['QuickSearchMixin'] = QuickSearchMixin;
		this.mixins['RefreshEntityObjectDataMixin'] = RefreshEntityObjectDataMixin;
		this.mixins['SortableMixin'] = SortableMixin;
	}

	getOrNull(mixin)
	{
		return this.get(mixin, false);
	}

	get(mixin, fail = true)
	{
		if (this.mixins[mixin]) {
			return this.mixins[mixin];
		} else {
			if (fail) {
				throw "Mixin '" + mixin + "' hasn't been registered.";
			} else {
				return null;
			}
		}
	}
}

export default MixinProvider