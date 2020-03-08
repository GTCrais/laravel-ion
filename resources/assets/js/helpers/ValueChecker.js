
class ValueChecker
{
	empty(value) {
		return !this.notEmpty(value);
	}

	notEmpty(value) {
		return value !== null && value !== undefined && $.trim(value) !== '';
	}
}

export default ValueChecker;