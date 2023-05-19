// modules
import React, { useState, useEffect } from 'react';
import useFetch, { FILE_TYPE } from '../../hooks/useFetch';

// components
import { Button, Form, Message } from 'semantic-ui-react';
import { POST, PUT } from '../../constants/methods';
import { useDispatch } from 'react-redux';
import { addMessage } from '../../reducers/messages';
import MultiImageField from '../fields/MultiImageField';
import { Link } from 'react-router-dom';
import Wysiwyg from '../Wysiwyg';
import { buildFieldsMessage } from '../../functions';
import TagsField from '../fields/TagsField';
import useSaveToStorage from '../../hooks/useSaveToStorage';

const FormActuality = ({ handleSubmited, actuality = {}, tags = [] }) => {
	const dispatch = useDispatch();
	const [form, setForm] = useState(actuality);
	const [success, setSuccess] = useState(true);
	const [message, setMessage] = useState('');
	const [result, load, loading] = useFetch();
	const [resultImages, uploadImages, loadingImages] = useFetch();
	const [images, setImages] = useState([]);
	const [defaultWysiwyg, setDefaultWysiwyg] = useState(actuality.description);
	const saveStorage = stored => {
		setForm(stored);
		setDefaultWysiwyg(stored.description);
	};
	const [voidStorage] = useSaveToStorage(form, saveStorage);

	useEffect(() => {
		if (result) {
			setSuccess(result.success);
			if (result.success) {
				voidStorage();
				dispatch(addMessage(result.message, true));
				if (images.length) {
					handleUploadImages();
				} else {
					handleSubmited();
				}
			}

			if (result.errors) {
				setMessage(buildFieldsMessage(result.errors));
			} else if (result.message) {
				setMessage({ form: result.message });
			}
		}
	}, [result]);

	useEffect(() => {
		if (resultImages) {
			if (resultImages.success) {
				handleSubmited();
			} else {
				setSuccess(false);
				setMessage(resultImages.message);
			}
		}
	}, [resultImages]);

	const handleChange = (e, { name, value }) => setForm({ ...form, [name]: value });
	const handleChangeEditor = value => setForm({ ...form, description: value });
	const handleImages = (name, value) => setImages(value);
	const handleChangeTags = (name, tags) => setForm({ ...form, tags });

	const handleRemoveImage = i => {
		if (form.images[i]) {
			const images = form.images.slice();
			images.splice(i, 1);
			setForm({ ...form, images });
		}
	};

	const handleUploadImages = (body = images) => {
		uploadImages({
			url: `actualities/${result.actuality.id}/images`,
			method: POST,
			body,
			contentType: FILE_TYPE,
		});
	};

	const onSubmit = e => {
		e.preventDefault();
		load({
			url: actuality.id ? `actualities/${actuality.id}` : 'actualities',
			method: actuality.id ? PUT : POST,
			body: form,
		});
	};

	const getSelectedTags = () => {
		return tags.map(tag => {
			if (form.tags.findIndex(actualityTag => actualityTag.id === tag.id) > -1) {
				return { ...tag, selected: true };
			}
			return tag;
		});
	};

	return (
		<Form
			error={!success}
			success={success}
			onSubmit={onSubmit}
			loading={loading || loadingImages}
			className="mb-4"
		>
			<Form.Input
				name="title"
				label="Titre"
				defaultValue={form.title}
				placeholder={"Entrez le titre de l'actualité"}
				onChange={handleChange}
				required
				maxLength={255}
				message={message.title}
			/>
			<MultiImageField
				dirName="/images/actualities/"
				files={images}
				defaultImages={form.images}
				btnColor="orange"
				handleChange={handleImages}
				nbMax={5 - (form.images ? form.images.length : 0)}
				disabled={!!form.images && form.images.length > 4}
				handleRemove={handleRemoveImage}
			/>
			{tags.length > 0 && (
				<TagsField
					label="Catégories"
					name="tags"
					tags={form.tags ? getSelectedTags() : tags}
					handleChange={handleChangeTags}
				/>
			)}
			<Form.Input
				name="shortDescription"
				label="Description Courte"
				defaultValue={form.shortDescription}
				placeholder={'Description affichée en mode liste'}
				onChange={handleChange}
				maxLength={150}
				message={message.shortDescription}
			/>
			<Form.Field>
				<label>Description</label>
				<Wysiwyg
					defaultValue={defaultWysiwyg}
					placeholder={'Description affichée en mode page'}
					handleChange={handleChangeEditor}
					className="mb-0"
				/>
			</Form.Field>
			<Message error content={message} />
			<div className="text-center">
				<Button
					color="orange"
					type="submit"
					content="Valider"
					disabled={loading}
				/>
				<Button
					as={Link}
					to={'/entity/actualities/' + (actuality.id || '')}
					color="grey"
					content="Annuler"
				/>
			</div>
		</Form>
	);
};
export default FormActuality;
