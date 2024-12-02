class DivisionRouter:
    def db_for_read(self, model, **hints):
        division = hints.get('division')
        if division == 'Dhaka':
            return 'dhaka'
        elif division == 'Chittagong':
            return 'chittagong'
        return 'default'

    def db_for_write(self, model, **hints):
        division = hints.get('division')
        print(f"Router received division for write: '{division}'")  # Debug output
        if division == 'Dhaka':
            return 'dhaka'
        elif division == 'Chittagong':
            return 'chittagong'
        return 'default'

    def allow_relation(self, obj1, obj2, **hints):
        db_set = {'dhaka', 'chittagong', 'default'}
        if obj1._state.db in db_set and obj2._state.db in db_set:
            return True
        return None

    def allow_migrate(self, db, app_label, model_name=None, **hints):
        if db == 'dhaka':
            return model_name in ['location', 'crime']
        if db == 'chittagong':
            return model_name in ['location', 'crime']
        if db == 'default':
            return model_name in ['appuser']
        return False
