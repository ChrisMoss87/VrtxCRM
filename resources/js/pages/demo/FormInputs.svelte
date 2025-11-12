<script lang="ts">
	import { Button } from '@/components/ui/button';
	import * as Card from '@/components/ui/card';
	import { Separator } from '@/components/ui/separator';
	import {
		TextField,
		TextareaField,
		SelectField
	} from '@/components/form';
    import { Checkbox } from "@/components/ui/checkbox/index.js";
    import * as Field from "@/components/ui/field/index.js";
    import { Input } from "@/components/ui/input/index.js";
    import * as Select from "@/components/ui/select/index.js";
    import { Textarea } from "@/components/ui/textarea/index.js";

    let month = $state<string>();
    let year = $state<string>();
	// Form state
	let firstName = $state('');
	let lastName = $state('');
	let email = $state('');
	let phone = $state('');
	let password = $state('');
	let website = $state('');
	let age = $state('');
	let bio = $state('');
	let country = $state('');
	let status = $state('');
	let agreeToTerms = $state(false);
	let receiveEmails = $state(false);
	let enableNotifications = $state(true);

	// Validation errors (simulated)
	let errors = $state<Record<string, string>>({});

	// Options for selects
	const countryOptions = [
		{ value: 'us', label: 'United States' },
		{ value: 'uk', label: 'United Kingdom' },
		{ value: 'ca', label: 'Canada' },
		{ value: 'au', label: 'Australia' },
		{ value: 'de', label: 'Germany' }
	];

	const statusOptions = [
		{ value: 'active', label: 'Active' },
		{ value: 'inactive', label: 'Inactive' },
		{ value: 'pending', label: 'Pending' },
		{ value: 'suspended', label: 'Suspended' }
	];

	function handleSubmit() {
		// Clear previous errors
		errors = {};

		// Simple validation
		if (!firstName.trim()) errors.first_name = 'First name is required';
		if (!lastName.trim()) errors.last_name = 'Last name is required';
		if (!email.trim()) errors.email = 'Email is required';
		else if (!/\S+@\S+\.\S+/.test(email)) errors.email = 'Email format is invalid';
		if (!agreeToTerms) errors.agree_to_terms = 'You must agree to the terms';

		if (Object.keys(errors).length === 0) {
			alert('Form submitted successfully! Check console for values.');
			console.log({
				firstName,
				lastName,
				email,
				phone,
				password,
				website,
				age,
				bio,
				country,
				status,
				agreeToTerms,
				receiveEmails,
				enableNotifications
			});
		}
	}
</script>

<div class="container mx-auto py-8 max-w-5xl">
	<div class="space-y-6">
		<div>
			<h1 class="text-3xl font-bold tracking-tight">Form Input Components Demo</h1>
			<p class="text-muted-foreground mt-2">
				Demonstration of all available form input wrapper components with shadcn-svelte
			</p>
		</div>

		<Separator />

		<form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }} class="space-y-8">
			<!-- Text Inputs Section -->
			<Card.Root>
				<Card.Header>
					<Card.Title>Text Input Fields</Card.Title>
					<Card.Description>
						TextFieldWrapper component with different input types
					</Card.Description>
				</Card.Header>
				<Card.Content class="space-y-4">
					<div class="flex flex-wrap gap-4">
						<TextFieldWrapper
							label="First Name"
							name="first_name"
							bind:value={firstName}
							placeholder="Enter your first name"
							description="Your legal first name"
							required
							width={50}
							error={errors.first_name}
						/>

						<TextFieldWrapper
							label="Last Name"
							name="last_name"
							bind:value={lastName}
							placeholder="Enter your last name"
							description="Your legal last name"
							required
							width={50}
							error={errors.last_name}
						/>

						<TextFieldWrapper
							label="Email Address"
							name="email"
							type="email"
							bind:value={email}
							placeholder="you@example.com"
							helpText="We'll never share your email"
							required
							width={50}
							error={errors.email}
						/>

						<TextFieldWrapper
							label="Phone Number"
							name="phone"
							type="tel"
							bind:value={phone}
							placeholder="+1 (555) 000-0000"
							description="Include country code"
							width={25}
						/>

						<TextFieldWrapper
							label="Password"
							name="password"
							type="password"
							bind:value={password}
							placeholder="Enter a strong password"
							helpText="Must be at least 8 characters"
							required
							width={50}
						/>

						<TextFieldWrapper
							label="Website"
							name="website"
							type="url"
							bind:value={website}
							placeholder="https://example.com"
							description="Your personal or company website"
							width={50}
						/>

						<TextFieldWrapper
							label="Age"
							name="age"
							type="number"
							bind:value={age}
							placeholder="25"
							helpText="Must be 18 or older"
							width={25}
						/>
					</div>
				</Card.Content>
			</Card.Root>

			<!-- Textarea Section -->
			<Card.Root>
				<Card.Header>
					<Card.Title>Textarea Fields</Card.Title>
					<Card.Description>TextareaFieldWrapper for multi-line text input</Card.Description>
				</Card.Header>
				<Card.Content>
					<TextareaFieldWrapper
						label="Bio"
						name="bio"
						bind:value={bio}
						placeholder="Tell us about yourself..."
						description="A brief description about you"
						helpText="Maximum 500 characters"
						rows={6}
						maxlength={500}
					/>
				</Card.Content>
			</Card.Root>

			<!-- Select Section -->
			<Card.Root>
				<Card.Header>
					<Card.Title>Select Dropdowns</Card.Title>
					<Card.Description>SelectFieldWrapper with options</Card.Description>
				</Card.Header>
				<Card.Content class="space-y-4">
					<div class="flex flex-wrap gap-4">
						<SelectFieldWrapper
							label="Country"
							name="country"
							bind:value={country}
							options={countryOptions}
							placeholder="Select your country"
							description="Country of residence"
							width={50}
						/>

						<SelectFieldWrapper
							label="Account Status"
							name="status"
							bind:value={status}
							options={statusOptions}
							placeholder="Select status"
							description="Current account status"
							width={50}
							required
						/>
					</div>
				</Card.Content>
			</Card.Root>

			<!-- Checkbox Section -->
			<Card.Root>
				<Card.Header>
					<Card.Title>Checkbox Fields</Card.Title>
					<Card.Description>CheckboxFieldWrapper for boolean inputs</Card.Description>
				</Card.Header>
				<Card.Content class="space-y-4">
					<CheckboxFieldWrapper
						label="I agree to the terms and conditions"
						name="agree_to_terms"
						bind:checked={agreeToTerms}
						description="You must accept our terms and conditions to continue"
						required
						error={errors.agree_to_terms}
					/>

					<CheckboxFieldWrapper
						label="Subscribe to newsletter"
						name="receive_emails"
						bind:checked={receiveEmails}
						description="Receive weekly updates about new features and products"
						helpText="You can unsubscribe at any time"
					/>
				</Card.Content>
			</Card.Root>

			<!-- Switch/Toggle Section -->
			<Card.Root>
				<Card.Header>
					<Card.Title>Toggle Switches</Card.Title>
					<Card.Description>SwitchFieldWrapper for toggle controls</Card.Description>
				</Card.Header>
				<Card.Content>
					<SwitchFieldWrapper
						label="Enable Notifications"
						name="enable_notifications"
						bind:checked={enableNotifications}
						description="Receive push notifications for important updates"
						helpText="Can be changed anytime in settings"
					/>
				</Card.Content>
			</Card.Root>

			<!-- Submit Section -->
			<Card.Root>
				<Card.Content class="pt-6">
					<div class="flex gap-4">
						<Button type="submit">Submit Form</Button>
						<Button
							type="button"
							variant="outline"
							onclick={() => {
								firstName = '';
								lastName = '';
								email = '';
								phone = '';
								password = '';
								website = '';
								age = '';
								bio = '';
								country = '';
								status = '';
								agreeToTerms = false;
								receiveEmails = false;
								enableNotifications = true;
								errors = {};
							}}
						>
							Reset
						</Button>
					</div>
				</Card.Content>
			</Card.Root>
		</form>

		<!-- Current Values Display -->
		<Card.Root>
			<Card.Header>
				<Card.Title>Current Form Values</Card.Title>
				<Card.Description>Live preview of form state</Card.Description>
			</Card.Header>
			<Card.Content>
				<pre class="bg-muted p-4 rounded-lg overflow-auto text-xs">
{JSON.stringify(
	{
		firstName,
		lastName,
		email,
		phone,
		password: password ? '********' : '',
		website,
		age,
		bio,
		country,
		status,
		agreeToTerms,
		receiveEmails,
		enableNotifications
	},
	null,
	2
)}</pre>
			</Card.Content>
		</Card.Root>
	</div>
</div>


<div class="w-full max-w-md m-auto">
    <form>
        <Field.Group>
            <Field.Set>
                <Field.Legend>Payment Method</Field.Legend>
                <Field.Description
                >All transactions are secure and encrypted</Field.Description
                >
                <Field.Group>
                    <Field.Field>
                        <Field.Label for="checkout-7j9-card-name-43j"
                        >Name on Card</Field.Label
                        >
                        <Input
                            id="checkout-7j9-card-name-43j"
                            placeholder="Evil Rabbit"
                            required
                        />
                    </Field.Field>
                    <Field.Field>
                        <Field.Label for="checkout-7j9-card-number-uw1"
                        >Card Number</Field.Label
                        >
                        <Input
                            id="checkout-7j9-card-number-uw1"
                            placeholder="1234 5678 9012 3456"
                            required
                        />
                        <Field.Description
                        >Enter your 16-digit card number</Field.Description
                        >
                    </Field.Field>
                    <div class="grid grid-cols-3 gap-4">
                        <Field.Field>
                            <Field.Label for="checkout-exp-month-ts6">Month</Field.Label>
                            <Select.Root type="single" bind:value={month}>
                                <Select.Trigger id="checkout-exp-month-ts6">
         <span>
          {month || "MM"}
         </span>
                                </Select.Trigger>
                                <Select.Content>
                                    <Select.Item value="01">01</Select.Item>
                                    <Select.Item value="02">02</Select.Item>
                                    <Select.Item value="03">03</Select.Item>
                                    <Select.Item value="04">04</Select.Item>
                                    <Select.Item value="05">05</Select.Item>
                                    <Select.Item value="06">06</Select.Item>
                                    <Select.Item value="07">07</Select.Item>
                                    <Select.Item value="08">08</Select.Item>
                                    <Select.Item value="09">09</Select.Item>
                                    <Select.Item value="10">10</Select.Item>
                                    <Select.Item value="11">11</Select.Item>
                                    <Select.Item value="12">12</Select.Item>
                                </Select.Content>
                            </Select.Root>
                        </Field.Field>
                        <Field.Field>
                            <Field.Label for="checkout-7j9-exp-year-f59">Year</Field.Label>
                            <Select.Root type="single" bind:value={year}>
                                <Select.Trigger id="checkout-7j9-exp-year-f59">
         <span>
          {year || "YYYY"}
         </span>
                                </Select.Trigger>
                                <Select.Content>
                                    <Select.Item value="2024">2024</Select.Item>
                                    <Select.Item value="2025">2025</Select.Item>
                                    <Select.Item value="2026">2026</Select.Item>
                                    <Select.Item value="2027">2027</Select.Item>
                                    <Select.Item value="2028">2028</Select.Item>
                                    <Select.Item value="2029">2029</Select.Item>
                                </Select.Content>
                            </Select.Root>
                        </Field.Field>
                        <Field.Field>
                            <Field.Label for="checkout-7j9-cvv">CVV</Field.Label>
                            <Input id="checkout-7j9-cvv" placeholder="123" required />
                        </Field.Field>
                    </div>
                </Field.Group>
            </Field.Set>
            <Field.Separator />
            <Field.Set>
                <Field.Legend>Billing Address</Field.Legend>
                <Field.Description>
                    The billing address associated with your payment method
                </Field.Description>
                <Field.Group>
                    <Field.Field orientation="horizontal">
                        <Checkbox id="checkout-7j9-same-as-shipping-wgm" checked={true} />
                        <Field.Label
                            for="checkout-7j9-same-as-shipping-wgm"
                            class="font-normal"
                        >
                            Same as shipping address
                        </Field.Label>
                    </Field.Field>
                </Field.Group>
            </Field.Set>
            <Field.Separator />
            <Field.Set>
                <Field.Group>
                    <Field.Field>
                        <Field.Label for="checkout-7j9-optional-comments"
                        >Comments</Field.Label
                        >
                        <Textarea
                            id="checkout-7j9-optional-comments"
                            placeholder="Add any additional comments"
                            class="resize-none"
                        />
                    </Field.Field>
                </Field.Group>
            </Field.Set>
            <Field.Field orientation="horizontal">
                <Button type="submit">Submit</Button>
                <Button variant="outline" type="button">Cancel</Button>
            </Field.Field>
        </Field.Group>
    </form>
</div>
